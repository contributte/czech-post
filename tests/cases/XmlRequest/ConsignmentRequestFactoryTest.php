<?php declare(strict_types = 1);

namespace Tests\Cases\XmlRequest;

use Contributte\CzechPost\XmlRequest\ConsignmentRequestFactory;
use Tester\Assert;
use Tester\TestCase;
use Tests\Toolkit\Tests;

require __DIR__ . '/../../bootstrap.php';

final class ConsignmentRequestFactoryTest extends TestCase
{

	/** @var ConsignmentRequestFactory */
	private $factory;

	public function setUp(): void
	{
		$this->factory = new ConsignmentRequestFactory();
	}

	public function testCreateWithoutCheque(): void
	{
		$consignment = Tests::createConsignmentWithoutCheque();

		$request = $this->factory->create($consignment);
		Assert::same(file_get_contents(__DIR__ . '/../../fixtures/examples/without_cheque.xml'), $request->saveXML());
	}

	public function testCreateWithCheque(): void
	{
		$consignment = Tests::createConsignmentWithCheque();

		$request = $this->factory->create($consignment);
		Assert::same(file_get_contents(__DIR__ . '/../../fixtures/examples/with_cheque.xml'), $request->saveXML());
	}

	private function assertFileContent(string $file, string $generated): void
	{
		$expected = file_get_contents($file);
		Assert::same($expected, $generated);
	}

}

(new ConsignmentRequestFactoryTest())->run();
