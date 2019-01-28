<?php declare(strict_types = 1);

namespace Tests\Cases\Contributte\CzechPost;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\Http\GuzzleClient;
use Contributte\CzechPost\Requestor\ConsignmentRequestor;
use Contributte\CzechPost\Utils\Helpers;
use DateTime;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Tests\Cases\Contributte\CzechPost\XmlRequest\ConsignmentRequestFactoryTest;

final class UsageTest extends TestCase
{

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
				'tmp_dir' => __DIR__ . '/../../../tmp/',
			],
		];

		$guzzle = new GuzzleClient(new Client($config));
		$client = new ConsignmentClient($guzzle, $config);
		$this->cpost = new ConsignmentRequestor($client);
	}

	public function testSendConsignment(): string
	{
		$res = $this->cpost->sendConsignment(ConsignmentRequestFactoryTest::createConsignmentWithoutCheque());
		$xml = Helpers::xmlToArray($res->getBody()->getContents());

		$this->assertConsignmentAccepted($xml);

		return $xml['kod_objednavky'];
	}

	public function testSendConsignmentWithCheque(): void
	{
		$res = $this->cpost->sendConsignment(ConsignmentRequestFactoryTest::createConsignmentWithCheque());
		$xml = Helpers::xmlToArray($res->getBody()->getContents());

		$this->assertConsignmentAccepted($xml);
	}

	/**
	 * @depends testSendConsignment
	 */
	public function testGetConsignmentsOverview(string $consignmentCode): void
	{
		$res = $this->cpost->getConsignmentsOverview($consignmentCode);
		$xml = Helpers::xmlToArray($res->getBody()->getContents());

		$this->assertEquals($consignmentCode, $xml['zakazka']['@attributes']['id']);
		$this->assertNotNull($xml['zakazka']['podacicislo']);
		$this->assertEquals(1, $xml['zakazka']['pocetstranek']);
		$this->assertGreaterThanOrEqual(28, (float) $xml['zakazka']['cena']);
		$this->assertEquals((new DateTime())->format('Y-m-d'), explode(' ', $xml['zakazka']['datumpodani'], 2)[0]);
	}

	public function testGetConsignmentsOverviewInvalidId(): void
	{
		$res = $this->cpost->getConsignmentsOverview('some-invalid-id');
		$xml = Helpers::xmlToArray($res->getBody()->getContents());

		$this->assertEquals(-9, $xml['kod']);
		$this->assertEquals('Zakázka č. some-invalid-id neexistuje.', $xml['popis']);
	}

	public function testGetConsignmentByDate(): void
	{
		$today = new DateTime();
		$res = $this->cpost->getConsignmentsByDate($today);
		$xml = Helpers::xmlToArray($res->getBody()->getContents());

		$this->assertArrayHasKey('zakazka', $xml);
		$this->assertGreaterThanOrEqual(1, count($xml['zakazka']));
	}

	public function testGetConsignmentByDateNoRecords(): void
	{
		$longTimeAgo = (new DateTime())->setDate(1975, 12, 24);
		$res = $this->cpost->getConsignmentsByDate($longTimeAgo);
		$xml = Helpers::xmlToArray($res->getBody()->getContents());

		$this->assertArrayHasKey('kod', $xml);
		$this->assertEquals('-10', $xml['kod']);
		$this->assertArrayHasKey('popis', $xml);
		$this->assertContains('neexistuje žádný záznam', $xml['popis']);
	}

	/**
	 * @param mixed[] $xml
	 */
	private function assertConsignmentAccepted(array $xml): void
	{
		$this->assertArrayHasKey('chyby', $xml);
		$this->assertEquals(0, $xml['chyby']['@attributes']['stav']);

		$this->assertArrayHasKey('kod_objednavky', $xml);
		$this->assertEquals(20, strlen($xml['kod_objednavky']));
	}

}
