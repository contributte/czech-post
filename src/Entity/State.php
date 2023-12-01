<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Entity;

use DateTimeImmutable;

final class State
{

	private string $id;

	private DateTimeImmutable $date;

	private string $text;

	private ?string $postCode = null;

	private ?string $postOffice = null;

	private ?DateTimeImmutable $deliveryAttempt = null;

	/**
	 * @param mixed[] $data
	 */
	public static function fromArray(array $data): self
	{
		$st = new self();
		$st->id = $data['id'];
		$st->date = new DateTimeImmutable($data['date']);
		$st->text = $data['text'] ?? '';

		$st->postCode = $data['postcode'] ?? null;
		$st->postOffice = $data['postoffice'] ?? null;
		$st->deliveryAttempt = isset($data['timeDeliveryAttempt']) ?
			new DateTimeImmutable($data['timeDeliveryAttempt']) :
			null;

		return $st;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function getPostCode(): ?string
	{
		return $this->postCode;
	}

	public function getPostOffice(): ?string
	{
		return $this->postOffice;
	}

	public function getDeliveryAttempt(): ?DateTimeImmutable
	{
		return $this->deliveryAttempt;
	}

}
