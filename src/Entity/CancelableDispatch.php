<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Entity;

use Contributte\CzechPost\Enum\Status;
use Contributte\CzechPost\Exception\Logical\InvalidStateException;

final class CancelableDispatch
{

	private string $id;

	private int $status = Status::PENDING;

	private string $description = '';

	public function __construct(string $id, int $status, string $description)
	{
		$this->id = $id;
		$this->status = $status;
		$this->description = $description;
	}

	/**
	 * @param mixed[] $data
	 */
	public static function fromArray(array $data): self
	{
		if (!isset($data['@attributes']) || !isset($data['@attributes']['cislo'])) {
			throw new InvalidStateException('Cannot get consingnment\'s "cislo" from array.');
		}

		return new self(
			$data['@attributes']['cislo'],
			isset($data['stav']) ? (int) $data['stav'] : Status::PENDING,
			$data['popis'] ?? ''
		);
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getStatus(): int
	{
		return $this->status;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

}
