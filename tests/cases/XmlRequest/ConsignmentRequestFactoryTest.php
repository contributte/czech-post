<?php declare(strict_types = 1);

namespace Tests\Cases\Contributte\CzechPost\XmlRequest;

use Contributte\CzechPost\XmlRequest\Consignment\Cheque;
use Contributte\CzechPost\XmlRequest\Consignment\Consignment;
use Contributte\CzechPost\XmlRequest\Consignment\File;
use Contributte\CzechPost\XmlRequest\Consignment\Person;
use Contributte\CzechPost\XmlRequest\ConsignmentRequestFactory;
use PHPUnit\Framework\TestCase;

final class ConsignmentRequestFactoryTest extends TestCase
{

	/** @var ConsignmentRequestFactory */
	private $factory;

	public function setUp(): void
	{
		$this->factory = new ConsignmentRequestFactory();
	}

	public static function createConsignmentWithoutCheque(): Consignment
	{
		$file = new File('example.pdf', base64_encode(file_get_contents(__DIR__ . '/examples/sample.pdf')), 0);
		$cons = new Consignment([$file]);
		$cons->setPaymentType(169);

		$sender = new Person(Person::TYPE_SENDER);
		$sender->setCompany('ISP Alliance s.r.o.');
		$sender->setFullName('Michal David');
		$sender->setStreet('Liberecká');
		$sender->setStreetNumber('5');
		$sender->setMunicipality('Jablonec n. Nisou');
		$sender->setPostcode('50040');

		$recipient = new Person(Person::TYPE_RECIPIENT);
		$recipient->setSalutation('Dear.');
		$recipient->setFullName('Mr. John Doe');
		$recipient->setCompany('Doe partners inc.');
		$recipient->setStreet('O\'Connel Street');
		$recipient->setStreetNumber('123');
		$recipient->setOrientationNumber('24/a');
		$recipient->setMunicipality('Dublin');
		$recipient->setPostcode('15021');
		$recipient->setCountry('ie');

		$cons->setSender($sender);
		$cons->setRecipient($recipient);

		return $cons;
	}

	public static function createConsignmentWithCheque(): Consignment
	{
		$cons = self::createConsignmentWithoutCheque();

		$cheque = new Cheque();
		$cheque->setPrice('199');
		$cheque->setBankAccountPrefix('43');
		$cheque->setBankAccountNumber('14680298');
		$cheque->setBankCode('0100');
		$cheque->setVariableSymbol('2018100301');
		$cheque->setCommentLineOne('Testovaci poukazka');
		$cheque->setPurpose('Testovaci poukazka');

		$cheque->addSenderAddressLine('Jmeno platce');
		$cheque->addSenderAddressLine('Adresa platce');

		$cheque->addRecipientAddressLine('Jmeno prijemce');
		$cheque->addRecipientAddressLine('Adresa prijemce');

		$cons->setCheque($cheque);

		return $cons;
	}

	public function testCreateWithoutCheque(): void
	{
		$consignment = self::createConsignmentWithoutCheque();

		$request = $this->factory->create($consignment);
		$this->assertFileContent(__DIR__ . '/examples/without_cheque.xml', $request);
	}

	public function testCreateWithCheque(): void
	{
		$consignment = self::createConsignmentWithCheque();

		$request = $this->factory->create($consignment);
		$this->assertFileContent(__DIR__ . '/examples/with_cheque.xml', $request);
	}

	private function assertFileContent(string $file, string $generated): void
	{
		$expected = file_get_contents($file);
		$this->assertEquals($expected, $generated);
	}

}
