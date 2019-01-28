<?php declare(strict_types = 1);

namespace Contributte\CzechPost\XmlRequest\Consignment;

use InvalidArgumentException;

final class Cheque
{

	private const MAX_ADDRESS_LINES_COUNT = 5; // defined by CPost

	/** @var string */
	private $price = '0';

	/** @var string */
	private $bankAccountPrefix = '';

	/** @var string */
	private $bankAccountNumber = '';

	/** @var string */
	private $bankCode = '';

	/** @var string */
	private $variableSymbol = '';

	/** @var string */
	private $specificSymbol = '';

	/** @var string */
	private $constantSymbol = '';

	/** @var string */
	private $commentLineOne = '';

	/** @var string */
	private $commentLineTwo = '';

	/** @var string */
	private $purpose = '';

	/** @var string[] */
	private $senderAddressLines = [];

	/** @var string[] */
	private $recipientAddressLines = [];

	public function getPrice(): string
	{
		return $this->price;
	}

	public function setPrice(string $price): void
	{
		$this->price = $price;
	}

	public function getBankAccountPrefix(): string
	{
		return $this->bankAccountPrefix;
	}

	public function setBankAccountPrefix(string $bankAccountPrefix): void
	{
		$this->bankAccountPrefix = $bankAccountPrefix;
	}

	public function getBankAccountNumber(): string
	{
		return $this->bankAccountNumber;
	}

	public function setBankAccountNumber(string $bankAccountNumber): void
	{
		$this->bankAccountNumber = $bankAccountNumber;
	}

	public function getBankCode(): string
	{
		return $this->bankCode;
	}

	public function setBankCode(string $bankCode): void
	{
		$this->bankCode = $bankCode;
	}

	public function getVariableSymbol(): string
	{
		return $this->variableSymbol;
	}

	public function setVariableSymbol(string $variableSymbol): void
	{
		if (strlen($variableSymbol) > 10) {
			throw new InvalidArgumentException('Variable symbol must not be longer then 10 characters');
		}

		$this->variableSymbol = $variableSymbol;
	}

	public function getSpecificSymbol(): string
	{
		return $this->specificSymbol;
	}

	public function setSpecificSymbol(string $specificSymbol): void
	{
		$this->specificSymbol = $specificSymbol;
	}

	public function getConstantSymbol(): string
	{
		return $this->constantSymbol;
	}

	public function setConstantSymbol(string $constantSymbol): void
	{
		$this->constantSymbol = $constantSymbol;
	}

	public function getCommentLineOne(): string
	{
		return $this->commentLineOne;
	}

	public function setCommentLineOne(string $commentLineOne): void
	{
		$this->commentLineOne = $commentLineOne;
	}

	public function getCommentLineTwo(): string
	{
		return $this->commentLineTwo;
	}

	public function setCommentLineTwo(string $commentLineTwo): void
	{
		$this->commentLineTwo = $commentLineTwo;
	}

	public function getPurpose(): string
	{
		return $this->purpose;
	}

	public function setPurpose(string $purpose): void
	{
		$this->purpose = $purpose;
	}

	public function addSenderAddressLine(string $line): void
	{
		$this->senderAddressLines[] = $line;
	}

	public function addRecipientAddressLine(string $line): void
	{
		$this->recipientAddressLines[] = $line;
	}

	/**
	 * @return string[]
	 */
	public function getSenderAddressLines(): array
	{
		return $this->sliceAddressLines($this->senderAddressLines);
	}

	/**
	 * @return string[]
	 */
	public function getRecipientAddressLines(): array
	{
		return $this->sliceAddressLines($this->recipientAddressLines);
	}

	/**
	 * @param string[] $arr
	 * @return string[]
	 */
	private function sliceAddressLines(array $arr): array
	{
		$values = array_values(array_slice($arr, 0, self::MAX_ADDRESS_LINES_COUNT - 1));

		return array_pad($values, 4, '');
	}

}
