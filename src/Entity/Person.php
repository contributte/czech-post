<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Entity;

final class Person
{

	public const TYPE_SENDER = 'sender';
	public const TYPE_RECIPIENT = 'recipient';

	/** @var string */
	private $type;

	/** @var string */
	private $salutation = '';

	/** @var string */
	private $company = '';

	/** @var string */
	private $fullName = '';

	/** @var string */
	private $street = '';

	/** @var string */
	private $streetNumber = '';

	/** @var string */
	private $orientationNumber = '';

	/** @var string */
	private $municipality = '';

	/** @var string */
	private $postcode = '';

	/** @var string */
	private $country = '';

	public function __construct(string $type)
	{
		$this->type = $type;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): void
	{
		$this->type = $type;
	}

	public function getSalutation(): string
	{
		return $this->salutation;
	}

	public function setSalutation(string $salutation): void
	{
		$this->salutation = $salutation;
	}

	public function getCompany(): string
	{
		return $this->company;
	}

	public function setCompany(string $company): void
	{
		$this->company = $company;
	}

	public function getFullName(): string
	{
		return $this->fullName;
	}

	public function setFullName(string $fullName): void
	{
		$this->fullName = $fullName;
	}

	public function getStreet(): string
	{
		return $this->street;
	}

	public function setStreet(string $street): void
	{
		$this->street = $street;
	}

	public function getStreetNumber(): string
	{
		return $this->streetNumber;
	}

	public function setStreetNumber(string $streetNumber): void
	{
		$this->streetNumber = $streetNumber;
	}

	public function getOrientationNumber(): string
	{
		return $this->orientationNumber;
	}

	public function setOrientationNumber(string $orientationNumber): void
	{
		$this->orientationNumber = $orientationNumber;
	}

	public function getMunicipality(): string
	{
		return $this->municipality;
	}

	public function setMunicipality(string $municipality): void
	{
		$this->municipality = $municipality;
	}

	public function getPostcode(): string
	{
		return $this->postcode;
	}

	public function setPostcode(string $postcode): void
	{
		$this->postcode = $postcode;
	}

	public function getCountry(): string
	{
		return $this->country;
	}

	public function setCountry(string $country): void
	{
		$this->country = $country;
	}

}
