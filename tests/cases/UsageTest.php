<?php declare(strict_types = 1);

namespace Tests\Cases\Contributte\CzechPost;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\Entity\CancelableDispatch;
use Contributte\CzechPost\Entity\Dispatch;
use Contributte\CzechPost\Exception\Runtime\ResponseException;
use Contributte\CzechPost\Http\GuzzleClient;
use Contributte\CzechPost\Requestor\ConsignmentRequestor;
use DateTimeImmutable;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Tests\Cases\Contributte\CzechPost\XmlRequest\ConsignmentRequestFactoryTest;

final class UsageTest extends TestCase
{

	private const TMP_DIR = __DIR__ . '/../../tmp/';

	/** @var ConsignmentRequestor */
	private $cpost;

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
		$client = new ConsignmentClient($guzzle, $config);
		$this->cpost = new ConsignmentRequestor($client);
	}

	public function testSendConsignment(): Dispatch
	{
		$dispatch = $this->cpost->send(ConsignmentRequestFactoryTest::createConsignmentWithoutCheque());

		$this->assertConsignmentDispatch($dispatch);
		$this->assertEquals(20, strlen($dispatch->getId()));
		$this->assertFalse($dispatch->isPrintCheque());

		return $dispatch;
	}

	public function testSendConsignmentWithCheque(): void
	{
		$dispatch = $this->cpost->send(ConsignmentRequestFactoryTest::createConsignmentWithCheque());

		$this->assertConsignmentDispatch($dispatch);
		$this->assertEquals(20, strlen($dispatch->getId()));
		$this->assertTrue($dispatch->isPrintCheque());
	}

	/**
	 * @depends testSendConsignment
	 */
	public function testGetConsignmentsOverview(Dispatch $dispatch): void
	{
		$overview = $this->cpost->getDetail($dispatch->getId());
		$this->assertConsignmentDispatch($overview);
	}

	/**
	 * @depends testSendConsignment
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
		$this->cpost->getDetail('some-invalid-id');
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
		$this->assertCount(16, $types);
		$this->assertEquals('DOPORUČENĚ ST NEVRACET, VLOŽIT DO SCHRÁNKY ULOŽIT VŽDY', $types[169]);
	}

	public function testFetchIsoCodes(): void
	{
		$codes = $this->cpost->fetchIsoCodes();
		$this->assertCount(58, $codes);
		$this->assertEquals('Irsko', $codes['IE']);
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
