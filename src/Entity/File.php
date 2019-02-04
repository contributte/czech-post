<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Entity;

final class File
{

	/** @var string */
	private $fileName;

	/** @var string */
	private $content;

	/** @var int */
	private $printType = 0;

	/**
	 * Content is base64 content of a pdf file
	 */
	public function __construct(string $fileName, string $content, int $printType)
	{
		$this->fileName = $fileName;
		$this->content = $content;
		$this->printType = $printType;
	}

	public function getFileName(): string
	{
		return $this->fileName;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function getPrintType(): int
	{
		return $this->printType;
	}

}
