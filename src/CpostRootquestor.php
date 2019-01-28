<?php declare(strict_types = 1);

namespace Contributte\CzechPost;

use Contributte\CzechPost\Exception\Logical\InvalidStateException;
use Contributte\CzechPost\Requestor\AbstractRequestor;
use Contributte\CzechPost\Requestor\ConsignmentRequestor;

/**
 * @property-read ConsignmentRequestor $consignment
 */
class CpostRootquestor
{

	/** @var AbstractRequestor[] */
	private $requestors = [];

	public function add(string $name, AbstractRequestor $requestor): void
	{
		if (isset($this->requestors[$name])) {
			throw new InvalidStateException(sprintf('Requestor "%s" has been already registered.', $name));
		}

		$this->requestors[$name] = $requestor;
	}

	public function __get(string $name): AbstractRequestor
	{
		if (isset($this->requestors[$name])) {
			return $this->requestors[$name];
		}

		throw new InvalidStateException(sprintf('Undefined requestor "%s".', $name));
	}

}
