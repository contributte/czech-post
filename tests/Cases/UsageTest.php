<?php declare(strict_types = 1);

namespace Tests\Cases;

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
use Ninjify\Nunjuck\TestCase\BaseTestCase;
use Tester\Assert;
use Tester\Environment;
use Tests\Cases\XmlRequest\ConsignmentRequestFactoryTest;

require_once __DIR__ . '/../bootstrap.php';

final class UsageTest extends BaseTestCase
{

	/** @var ConsignmentRequestor */
	private $cpost;

	/** @var ParcelHistoryRequestor */
	private $history;

	protected function setUp(): void
	{
		Environment::skip('This is integration test against Ceska Posta testing environment.');

		$config = [
			'http' => [
				'base_uri' => 'https://online3.postservis.cz/dopisonline/',
				'auth' => ['dreplech', 'dreplech'],
			],
			'config' => [
				'tmp_dir' => TEMP_DIR,
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
		Assert::equal(20, strlen($dispatch->getId()));
		Assert::false($dispatch->isPrintCheque());

		return $dispatch;
	}

	public function testSendWithCheque(): void
	{
		$dispatch = $this->cpost->send(ConsignmentRequestFactoryTest::createConsignmentWithCheque());

		$this->assertConsignmentDispatch($dispatch);
		Assert::equal(20, strlen($dispatch->getId()));
		Assert::true($dispatch->isPrintCheque());
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

		$fileName = sprintf('%s/%s.pdf', TEMP_DIR, $dispatch->getTrackingNumber());
		file_put_contents($fileName, $labelData);

		Assert::true(filesize($fileName) > 100000);
	}

	public function testGetConsignmentsOverviewInvalidId(): void
	{
		Assert::exception(function () {
			$this->cpost->detail('some-invalid-id');
		}, ResponseException::class, 'Error: Zakázka č. some-invalid-id neexistuje., Code: -9');
	}

	public function testGetConsignmentByDate(): void
	{
		$today = new DateTimeImmutable();
		$dispatches = $this->cpost->findByDate($today);

		// in previous test we created 1
		foreach ($dispatches as $d) {
			$this->assertConsignmentDispatch($d);
		}
	}

	public function testGetConsignmentByDateNoRecords(): void
	{
		Assert::exception(function () {
			$longTimeAgo = (new DateTimeImmutable())->setDate(1975, 12, 24);
			$this->cpost->findByDate($longTimeAgo);
		}, ResponseException::class, 'Error: K datu 19751224 neexistuje žádný záznam., Code: -10');
	}

	/**
	 * @return CancelableDispatch[]
	 */
	public function testListCancelable(): array
	{
		$toCancel = $this->cpost->listCancelable();
		foreach ($toCancel as $c) {
			Assert::equal(20, strlen($c->getId()));
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
		Assert::true(true);
	}

	public function testFetchPaymentTypes(): void
	{
		$types = $this->cpost->fetchPaymentTypes();
		Assert::count(2, $types);
		Assert::equal(['fakturou', 'SIPO'], $types);
	}

	public function testFetchPayOffTypes(): void
	{
		$types = $this->cpost->fetchPayOffTypes();
		Assert::count(32, $types);
		Assert::equal('DOPORUČENĚ ST PRIORITNĚ NEVRACET, VLOŽIT DO SCHRÁNKY ULOŽIT VŽDY', $types[169]);
	}

	public function testFetchIsoCodes(): void
	{
		$codes = $this->cpost->fetchIsoCodes();
		Assert::count(52, $codes);
		Assert::equal('Irsko (kromě Severního Irska)', $codes['IE']);
	}

	public function testParcelHistoryInvalidTrackingNumber(): void
	{
		Assert::exception(function () {
			$this->history->history('invalid');
		}, ResponseException::class, 'Parcel tracking error. State: -4, Description: "Pro tento druh zásilek Česká pošta informace nezobrazuje."');
	}

	public function testParcelHistoryParcelNotFound(): void
	{
		Assert::exception(function () {
			$this->history->history('RR2599903552');
		}, ResponseException::class, 'Parcel tracking error. State: -3, Description: "Zásilka tohoto podacího čísla není v evidenci."');
	}

	public function testParcelHistory(): void
	{
		$states = $this->history->history('DR3304881147U');

		Assert::count(12, $states);

		Assert::equal('Obdrženy údaje k zásilce.', $states[0]->getText());
		Assert::equal('2020-11-05', $states[0]->getDate()->format('Y-m-d'));
		Assert::null($states[0]->getPostCode());

		Assert::equal('Zásilka převzata do přepravy.', $states[1]->getText());
		Assert::equal('2020-11-05', $states[1]->getDate()->format('Y-m-d'));
		Assert::equal('10003', $states[1]->getPostCode());

		Assert::equal('Zásilka v přepravě.', $states[2]->getText());
		Assert::equal('2020-11-05', $states[2]->getDate()->format('Y-m-d'));
		Assert::null($states[2]->getPostCode());

		Assert::equal('Zásilka vypravena z třídícího centra.', $states[3]->getText());
		Assert::equal('2020-11-06', $states[3]->getDate()->format('Y-m-d'));
		Assert::equal('65502', $states[3]->getPostCode());

		Assert::equal('Přeprava zásilky k dodací poště.', $states[4]->getText());
		Assert::equal('2020-11-06', $states[4]->getDate()->format('Y-m-d'));
		Assert::null($states[4]->getPostCode());

		Assert::equal('E-mail adresátovi - zásilka převzata do přepravy.', $states[5]->getText());
		Assert::equal('2020-11-05', $states[5]->getDate()->format('Y-m-d'));
		Assert::null($states[5]->getPostCode());

		Assert::equal('SMS zpráva adresátovi - zásilka převzata do přepravy.', $states[6]->getText());
		Assert::equal('2020-11-05', $states[6]->getDate()->format('Y-m-d'));
		Assert::null($states[6]->getPostCode());

		Assert::equal('Příprava zásilky k doručení.', $states[7]->getText());
		Assert::equal('2020-11-09', $states[7]->getDate()->format('Y-m-d'));
		Assert::equal('60010', $states[7]->getPostCode());

		Assert::equal('E-mail adresátovi - termín doručení zásilky.', $states[8]->getText());
		Assert::equal('2020-11-09', $states[8]->getDate()->format('Y-m-d'));
		Assert::null($states[8]->getPostCode());

		Assert::equal('SMS zpráva adresátovi - termín doručení zásilky.', $states[9]->getText());
		Assert::equal('2020-11-09', $states[9]->getDate()->format('Y-m-d'));
		Assert::null($states[9]->getPostCode());

		Assert::equal('Doručování zásilky v rámci odpoledního doručování.', $states[10]->getText());
		Assert::equal('2020-11-09', $states[10]->getDate()->format('Y-m-d'));
		Assert::equal('60010', $states[10]->getPostCode());

		Assert::equal('Dodání zásilky.', $states[11]->getText());
		Assert::equal('2020-11-09', $states[11]->getDate()->format('Y-m-d'));
		Assert::equal('60010', $states[11]->getPostCode());
	}

	public function testParcelHistoryIsDelivered(): void
	{
		Assert::true($this->history->isDelivered('DR3304881147U'));
	}

	public function testParcelStatus(): void
	{
		$currentState = $this->history->status('DR3304881147U');

		Assert::equal('Dodání zásilky.', $currentState->getText());
		Assert::equal('2020-11-09', $currentState->getDate()->format('Y-m-d'));
		Assert::equal('60010', $currentState->getPostCode());
	}

	private function assertConsignmentDispatch(Dispatch $confirm): void
	{
		Assert::true(
			$confirm->getTrackingNumber() === 'neni' ||
			substr($confirm->getTrackingNumber(), 0, 2) === 'RR'
		);
		Assert::equal((new DateTimeImmutable())->format('Y-m-d'), $confirm->getDate()->format('Y-m-d'));
	}

}

(new UsageTest())->run();
