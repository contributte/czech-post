<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Entity;

use Contributte\CzechPost\Enum\Envelope;
use Contributte\CzechPost\Enum\PrintType;
use Contributte\CzechPost\Enum\Status;
use Contributte\CzechPost\Exception\Logical\InvalidStateException;
use DateTimeImmutable;

final class Dispatch
{

	/** @var mixed[] */
	private $rawData = [];

	/** @var string */
	private $id;

	/** @var string */
	private $trackingNumber = '';

	/** @var int */
	private $paymentType = 0;

	/** @var int */
	private $printType = PrintType::ONE_SIDED;

	/** @var int */
	private $envelope = Envelope::STANDARD;

	/** @var DateTimeImmutable|null */
	private $date;

	/** @var string */
	private $price = '';

	/** @var int */
	private $sheetsCount = 0;

	/** @var int */
	private $pagesCount = 0;

	/** @var bool */
	private $printCheque = false;

	/** @var int */
	private $payment = 0;

	/** @var int */
	private $status = Status::PENDING;

	/** @var string */
	private $postOffice = '';

	public function __construct(string $id, string $trackingNumber)
	{
		$this->id = $id;
		$this->trackingNumber = $trackingNumber;
	}

	/**
	 * @param mixed[] $data
	 */
	public static function fromArray(array $data): self
	{
		// fix inconsistent api responses (some calls return underscore in keys, some not)
		$clean = [];
		foreach ($data as $key => $value) {
			$newKey = str_replace('_', '', $key);
			$clean[$newKey] = $value;
		}

		$d = self::createEmptyDispatch($clean);
		$d->rawData = $data;

		if (isset($clean['datumpodani'])) {
			$d->date = new DateTimeImmutable($clean['datumpodani']);
		}

		$d->sheetsCount = isset($clean['pocetlistu']) ? (int) $clean['pocetlistu'] : 0;
		$d->pagesCount = isset($clean['pocetstranek']) ? (int) $clean['pocetstranek'] : 0;
		$d->price = isset($clean['cena']) ? (string) $clean['cena'] : '';
		$d->printType = isset($clean['typtisku']) ? (int) $clean['typtisku'] : PrintType::ONE_SIDED;
		$d->envelope = isset($clean['obalkac4']) ? (int) $clean['obalkac4'] : Envelope::STANDARD;
		$d->printCheque = isset($clean['tiskpoukazky']) ? (bool) $clean['tiskpoukazky'] : false;
		$d->paymentType = isset($clean['typvyplatneho']) ? (int) $clean['typvyplatneho'] : 0;

		// fields returned when sending new consignment
		$d->payment = isset($clean['platba']) ? (int) $clean['platba'] : 0;
		$d->status = isset($clean['zpracovani']) ? (int) $clean['zpracovani'] : Status::PENDING;

		// fields returned when getting consignment detail
		$d->postOffice = isset($clean['podaciposta']) ? (string) $clean['podaciposta'] : '';

		return $d;
	}

	/**
	 * @return mixed[]
	 */
	public function getRawData(): array
	{
		return $this->rawData;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getTrackingNumber(): string
	{
		return $this->trackingNumber;
	}

	public function getPaymentType(): int
	{
		return $this->paymentType;
	}

	public function getPrintType(): int
	{
		return $this->printType;
	}

	public function getEnvelope(): int
	{
		return $this->envelope;
	}

	public function getDate(): ?DateTimeImmutable
	{
		return $this->date;
	}

	public function getPrice(): string
	{
		return $this->price;
	}

	public function getSheetsCount(): int
	{
		return $this->sheetsCount;
	}

	public function getPagesCount(): int
	{
		return $this->pagesCount;
	}

	public function isPrintCheque(): bool
	{
		return $this->printCheque;
	}

	public function getPayment(): int
	{
		return $this->payment;
	}

	public function getStatus(): int
	{
		return $this->status;
	}

	public function getPostOffice(): string
	{
		return $this->postOffice;
	}

	/**
	 * @param mixed[] $data
	 */
	private static function createEmptyDispatch(array $data): self
	{
		if (!isset($data['podacicislo'])) {
			throw new InvalidStateException('Missing dispatch\'s  "podacicislo" key.');
		}

		// get unique consignment id
		$id = null;
		if (isset($data['@attributes']) && isset($data['@attributes']['id'])) {
			$id = (string) $data['@attributes']['id'];
		}
		if (isset($data['kodobjednavky'])) {
			$id = (string) $data['kodobjednavky'];
		}

		if ($id === null) {
			throw new InvalidStateException('Missing dispatch\'s  "id" or "kod_objednavky" key.');
		}

		return new self($id, (string) $data['podacicislo']);
	}

}
