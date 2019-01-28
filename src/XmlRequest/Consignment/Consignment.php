<?php declare(strict_types = 1);

namespace Contributte\CzechPost\XmlRequest\Consignment;

use Contributte\CzechPost\XmlRequest\Consignment\Enum\Envelope;
use Contributte\CzechPost\XmlRequest\Consignment\Enum\PrintType;

final class Consignment
{

	/** @var Person */
	private $sender;

	/** @var Person */
	private $recipient;

	/** @var int */
	private $paymentType = 0;

	/** @var int */
	private $printType = PrintType::ONE_SIDED;

	/** @var int */
	private $envelope = Envelope::STANDARD;

	/** @var bool */
	private $printCheque = false;

	/** @var int */
	private $printSenderType = PrintType::SENDER_USE_DETAILS;

	/** @var int */
	private $printRecipientType = PrintType::RECIPIENT_USE_DETAILS;

	/** @var File[] */
	private $files = [];

	/** @var Cheque|null */
	private $cheque;

	/**
	 * @param File[] $files
	 */
	public function __construct(array $files = [], ?Cheque $cheque = null)
	{
		$this->files = $files;
		$this->setCheque($cheque);
	}

	public function hasFiles(): bool
	{
		return count($this->files) > 0;
	}

	/**
	 * @return File[]
	 */
	public function getFiles(): array
	{
		return $this->files;
	}

	public function hasCheque(): bool
	{
		return $this->cheque !== null;
	}

	public function getCheque(): ?Cheque
	{
		return $this->cheque;
	}

	public function setCheque(?Cheque $cheque = null): void
	{
		if ($cheque !== null) {
			$this->printCheque = true;
		}

		$this->cheque = $cheque;
	}

	public function getSender(): Person
	{
		return $this->sender;
	}

	public function setSender(Person $sender): void
	{
		$this->sender = $sender;
	}

	public function getRecipient(): Person
	{
		return $this->recipient;
	}

	public function setRecipient(Person $recipient): void
	{
		$this->recipient = $recipient;
	}

	public function getPaymentType(): int
	{
		return $this->paymentType;
	}

	public function setPaymentType(int $paymentType): void
	{
		$this->paymentType = $paymentType;
	}

	public function getPrintType(): int
	{
		return $this->printType;
	}

	public function setPrintType(int $printType): void
	{
		$this->printType = $printType;
	}

	public function getEnvelope(): int
	{
		return $this->envelope;
	}

	public function setEnvelope(int $envelope): void
	{
		$this->envelope = $envelope;
	}

	public function isPrintCheque(): bool
	{
		return $this->printCheque;
	}

	public function setPrintCheque(bool $printCheque): void
	{
		$this->printCheque = $printCheque;
	}

	public function getPrintSenderType(): int
	{
		return $this->printSenderType;
	}

	public function setPrintSenderType(int $printSenderType): void
	{
		$this->printSenderType = $printSenderType;
	}

	public function getPrintRecipientType(): int
	{
		return $this->printRecipientType;
	}

	public function setPrintRecipientType(int $printRecipientType): void
	{
		$this->printRecipientType = $printRecipientType;
	}

}
