<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Requestor;

use Contributte\CzechPost\Exception\Runtime\ResponseException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractRequestor
{

	/**
	 * @param int[] $allowedStatusCodes
	 */
	protected function assertResponse(ResponseInterface $response, array $allowedStatusCodes = [200]): void
	{
		if (!in_array($response->getStatusCode(), $allowedStatusCodes, true)) {
			throw new ResponseException($response);
		}
	}

}
