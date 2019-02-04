<?php declare(strict_types = 1);

namespace Tests\Cases\Contributte\CzechPost;

use Contributte\CzechPost\Client\ConsignmentClient;
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
		$this->markTestSkipped('This is manual test');

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
		$dispatch = $this->cpost->sendConsignment(ConsignmentRequestFactoryTest::createConsignmentWithoutCheque());

		$this->assertConsignmentDispatch($dispatch);
		$this->assertEquals(20, strlen($dispatch->getId()));
		$this->assertFalse($dispatch->isPrintCheque());

		return $dispatch;
	}

	public function testSendConsignmentWithCheque(): void
	{
		$dispatch = $this->cpost->sendConsignment(ConsignmentRequestFactoryTest::createConsignmentWithCheque());

		$this->assertConsignmentDispatch($dispatch);
		$this->assertEquals(20, strlen($dispatch->getId()));
		$this->assertTrue($dispatch->isPrintCheque());
	}

	/**
	 * @depends testSendConsignment
	 */
	public function testGetConsignmentsOverview(Dispatch $dispatch): void
	{
		$overview = $this->cpost->getConsignmentOverview($dispatch->getId());
		$this->assertConsignmentDispatch($overview);
	}

	/**
	 * @depends testSendConsignment
	 */
	public function testGetConsignmentLabel(Dispatch $dispatch): void
	{
		$labelData = $this->cpost->getConsignmentLabel($dispatch->getTrackingNumber());

		$fileName = sprintf('%s/%s.pdf', self::TMP_DIR, $dispatch->getTrackingNumber());
		file_put_contents($fileName, $labelData);

		$this->assertGreaterThan(100000, filesize($fileName));
	}

	public function testGetConsignmentsOverviewInvalidId(): void
	{
		$this->expectException(ResponseException::class);
		$this->expectExceptionMessage('Error: Zakázka č. some-invalid-id neexistuje., Code: -9');
		$this->cpost->getConsignmentOverview('some-invalid-id');
	}

	public function testGetConsignmentByDate(): void
	{
		$today = new DateTimeImmutable();
		$dispatches = $this->cpost->getConsignmentsByDate($today);

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
		$this->cpost->getConsignmentsByDate($longTimeAgo);
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
