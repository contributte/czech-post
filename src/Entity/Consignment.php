<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Entity;

use Contributte\CzechPost\Enum\Envelope;
use Contributte\CzechPost\Enum\PrintType;

final class Consignment
{

	private Person $sender;

	private Person $recipient;

	private int $paymentType = 0;

	private int $printType = PrintType::ONE_SIDED;

	private int $envelope = Envelope::STANDARD;

	private bool $printCheque = false;

	private int $printSenderType = PrintType::SENDER_USE_DETAILS;

	private int $printRecipientType = PrintType::RECIPIENT_USE_DETAILS;

	private string $services = '';

	/** @var File[] */
	private array $files = [];

	private ?Cheque $cheque = null;

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

	public function getServices(): string
	{
		return $this->services;
	}

	public function setServices(string $services): void
	{
		$this->services = $services;
	}

}
