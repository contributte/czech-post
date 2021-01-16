<?php declare(strict_types = 1);

namespace Tests\Toolkit;

use Contributte\CzechPost\Entity\Cheque;
use Contributte\CzechPost\Entity\Consignment;
use Contributte\CzechPost\Entity\File;
use Contributte\CzechPost\Entity\Person;

final class Tests
{

	public const APP_PATH = __DIR__ . '/..';
	public const TEMP_PATH = __DIR__ . '/../tmp';

	public static function createConsignmentWithoutCheque(): Consignment
	{
		$file = new File('example.pdf', base64_encode(file_get_contents(__DIR__ . '/../fixtures/examples/sample.pdf')), 0);
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

}
