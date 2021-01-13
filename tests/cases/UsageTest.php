<?php declare(strict_types = 1);

namespace Tests\Cases\Contributte\CzechPost;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\Client\ParcelHistoryClient;
use Contributte\CzechPost\Entity\CancelableDispatch;
use Contributte\CzechPost\Entity\Dispatch;
use Contributte\CzechPost\Exception\Runtime\ResponseException;
use Contributte\CzechPost\Http\GuzzleClient;
use Contributte\CzechPost\Requestor\ConsignmentRequestor;
use Contributte\CzechPost\Requestor\ParcelHistoryRequestor;
use DateTimeImmutable;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Tests\Cases\Contributte\CzechPost\XmlRequest\ConsignmentRequestFactoryTest;

final class UsageTest extends TestCase
{

	private const TMP_DIR = __DIR__ . '/../../tmp/';

	/** @var ConsignmentRequestor */
	private $cpost;

	/** @var ParcelHistoryRequestor */
	private $history;

	protected function setUp(): void
	{
		$this->markTestSkipped('This is integration test against Ceska Posta testing environment.');

		$config = [
			'http' => [
				'base_uri' => 'https://online3.postservis.cz/dopisonline/',
				'auth' => ['dreplech', 'dreplech'],
			],
			'config' => [
				'tmp_dir' => self::TMP_DIR,
			],
		];

		$guzzle = new GuzzleClient(new Client($config['http']));

		$cpostClient = new ConsignmentClient($guzzle, $config);
		$this->cpost = new ConsignmentRequestor($cpostClient);

		$historyClient = new ParcelHistoryClient($guzzle);
		$this->history = new ParcelHistoryRequestor($historyClient);
	}

	public function testSend(): Dispatch
	{
		$dispatch = $this->cpost->send(ConsignmentRequestFactoryTest::createConsignmentWithoutCheque());

		$this->assertConsignmentDispatch($dispatch);
		$this->assertEquals(20, strlen($dispatch->getId()));
		$this->assertFalse($dispatch->isPrintCheque());

		return $dispatch;
	}

	public function testSendWithCheque(): void
	{
		$dispatch = $this->cpost->send(ConsignmentRequestFactoryTest::createConsignmentWithCheque());

		$this->assertConsignmentDispatch($dispatch);
		$this->assertEquals(20, strlen($dispatch->getId()));
		$this->assertTrue($dispatch->isPrintCheque());
	}

	/**
	 * @depends testSend
	 */
	public function testGetDetail(Dispatch $dispatch): void
	{
		$overview = $this->cpost->detail($dispatch->getId());
		$this->assertConsignmentDispatch($overview);
	}

	/**
	 * @depends testSend
	 */
	public function testGetConsignmentLabel(Dispatch $dispatch): void
	{
		$labelData = $this->cpost->printLabel($dispatch->getTrackingNumber());

		$fileName = sprintf('%s/%s.pdf', self::TMP_DIR, $dispatch->getTrackingNumber());
		file_put_contents($fileName, $labelData);

		$this->assertGreaterThan(100000, filesize($fileName));
	}

	public function testGetConsignmentsOverviewInvalidId(): void
	{
		$this->expectException(ResponseException::class);
		$this->expectExceptionMessage('Error: Zakázka č. some-invalid-id neexistuje., Code: -9');
		$this->cpost->detail('some-invalid-id');
	}

	public function testGetConsignmentByDate(): void
	{
		$today = new DateTimeImmutable();
		$dispatches = $this->cpost->findByDate($today);

		// in previous test we created 1
		$this->assertGreaterThanOrEqual(1, count($dispatches));
		foreach ($dispatches as $d) {
			$this->assertConsignmentDispatch($d);
		}
	}

	public function testGetConsignmentByDateNoRecords(): void
	{
		$this->expectException(ResponseException::class);
		$this->expectExceptionMessage('Error: K datu 19751224 neexistuje žádný záznam., Code: -10');

		$longTimeAgo = (new DateTimeImmutable())->setDate(1975, 12, 24);
		$this->cpost->findByDate($longTimeAgo);
	}

	/**
	 * @return CancelableDispatch[]
	 */
	public function testListCancelable(): array
	{
		$toCancel = $this->cpost->listCancelable();
		$this->assertGreaterThan(0, count($toCancel));
		foreach ($toCancel as $c) {
			$this->assertEquals(20, strlen($c->getId()));
		}

		return $toCancel;
	}

	/**
	 * @depends testListCancelable
	 * @param CancelableDispatch[] $toCancel
	 */
	public function testCancel(array $toCancel): void
	{
		$this->cpost->cancel($toCancel[count($toCancel) - 1]->getId());
		$this->assertTrue(true);
	}

	public function testFetchPaymentTypes(): void
	{
		$types = $this->cpost->fetchPaymentTypes();
		$this->assertCount(2, $types);
		$this->assertEquals(['fakturou', 'SIPO'], $types);
	}

	public function testFetchPayOffTypes(): void
	{
		$types = $this->cpost->fetchPayOffTypes();
		$this->assertCount(32, $types);
		$this->assertEquals('DOPORUČENĚ ST PRIORITNĚ NEVRACET, VLOŽIT DO SCHRÁNKY ULOŽIT VŽDY', $types[169]);
	}

	public function testFetchIsoCodes(): void
	{
		$codes = $this->cpost->fetchIsoCodes();
		$this->assertCount(52, $codes);
		$this->assertEquals('Irsko (kromě Severního Irska)', $codes['IE']);
	}

	public function testParcelHistoryInvalidTrackingNumber(): void
	{
		$this->expectException(ResponseException::class);
		$this->expectExceptionMessage(
			'Parcel tracking error. State: -4, Description: "Pro tento druh zásilek Česká pošta informace nezobrazuje."'
		);
		$this->history->history('invalid');
	}

	public function testParcelHistoryParcelNotFound(): void
	{
		$this->expectException(ResponseException::class);
		$this->expectExceptionMessage(
			'Parcel tracking error. State: -3, Description: "Zásilka tohoto podacího čísla není v evidenci."'
		);
		$this->history->history('RR2599903552');
	}

	public function testParcelHistory(): void
	{
		$states = $this->history->history('DR3304881147U');

		$this->assertCount(12, $states);

		$this->assertEquals('Obdrženy údaje k zásilce.', $states[0]->getText());
		$this->assertEquals('2020-11-05', $states[0]->getDate()->format('Y-m-d'));
		$this->assertNull($states[0]->getPostCode());

		$this->assertEquals('Zásilka převzata do přepravy.', $states[1]->getText());
		$this->assertEquals('2020-11-05', $states[1]->getDate()->format('Y-m-d'));
		$this->assertEquals('10003', $states[1]->getPostCode());

		$this->assertEquals('Zásilka v přepravě.', $states[2]->getText());
		$this->assertEquals('2020-11-05', $states[2]->getDate()->format('Y-m-d'));
		$this->assertNull($states[2]->getPostCode());

		$this->assertEquals('Zásilka vypravena z třídícího centra.', $states[3]->getText());
		$this->assertEquals('2020-11-06', $states[3]->getDate()->format('Y-m-d'));
		$this->assertEquals('65502', $states[3]->getPostCode());

		$this->assertEquals('Přeprava zásilky k dodací poště.', $states[4]->getText());
		$this->assertEquals('2020-11-06', $states[4]->getDate()->format('Y-m-d'));
		$this->assertNull($states[4]->getPostCode());

		$this->assertEquals('E-mail adresátovi - zásilka převzata do přepravy.', $states[5]->getText());
		$this->assertEquals('2020-11-05', $states[5]->getDate()->format('Y-m-d'));
		$this->assertNull($states[5]->getPostCode());

		$this->assertEquals('SMS zpráva adresátovi - zásilka převzata do přepravy.', $states[6]->getText());
		$this->assertEquals('2020-11-05', $states[6]->getDate()->format('Y-m-d'));
		$this->assertNull($states[6]->getPostCode());

		$this->assertEquals('Příprava zásilky k doručení.', $states[7]->getText());
		$this->assertEquals('2020-11-09', $states[7]->getDate()->format('Y-m-d'));
		$this->assertEquals('60010', $states[7]->getPostCode());

		$this->assertEquals('E-mail adresátovi - termín doručení zásilky.', $states[8]->getText());
		$this->assertEquals('2020-11-09', $states[8]->getDate()->format('Y-m-d'));
		$this->assertNull($states[8]->getPostCode());

		$this->assertEquals('SMS zpráva adresátovi - termín doručení zásilky.', $states[9]->getText());
		$this->assertEquals('2020-11-09', $states[9]->getDate()->format('Y-m-d'));
		$this->assertNull($states[9]->getPostCode());

		$this->assertEquals('Doručování zásilky v rámci odpoledního doručování.', $states[10]->getText());
		$this->assertEquals('2020-11-09', $states[10]->getDate()->format('Y-m-d'));
		$this->assertEquals('60010', $states[10]->getPostCode());

		$this->assertEquals('Dodání zásilky.', $states[11]->getText());
		$this->assertEquals('2020-11-09', $states[11]->getDate()->format('Y-m-d'));
		$this->assertEquals('60010', $states[11]->getPostCode());
	}

	public function testParcelHistoryIsDelivered(): void
	{
		$this->assertTrue($this->history->isDelivered('DR3304881147U'));
	}

	public function testParcelStatus(): void
	{
		$currentState = $this->history->status('DR3304881147U');

		$this->assertEquals('Dodání zásilky.', $currentState->getText());
		$this->assertEquals('2020-11-09', $currentState->getDate()->format('Y-m-d'));
		$this->assertEquals('60010', $currentState->getPostCode());
	}

	private function assertConsignmentDispatch(Dispatch $confirm): void
	{
		$this->assertTrue(
			$confirm->getTrackingNumber() === 'neni' ||
			substr($confirm->getTrackingNumber(), 0, 2) === 'RR'
		);
		$this->assertEquals((new DateTimeImmutable())->format('Y-m-d'), $confirm->getDate()->format('Y-m-d'));
	}

}
