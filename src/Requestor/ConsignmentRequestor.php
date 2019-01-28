<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Requestor;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\Exception\Logical\XmlException;
use Contributte\CzechPost\Exception\Runtime\ResponseException;
use Contributte\CzechPost\Utils\Helpers;
use Contributte\CzechPost\XmlRequest\Consignment\Consignment;
use DateTimeInterface;
use Psr\Http\Message\ResponseInterface;

class ConsignmentRequestor extends AbstractRequestor
{

	/** @var ConsignmentClient */
	protected $client;

	public function __construct(ConsignmentClient $client)
	{
		$this->client = $client;
	}

	public function sendConsignment(Consignment $consignment): ResponseInterface
	{
		$response = $this->client->sendConsignment($consignment);

		$this->assertResponse($response);

		return $response;
	}

	public function getConsignmentsOverview(string $consignmentId): ResponseInterface
	{
		$response = $this->client->getConsignment($consignmentId);

		$this->assertResponse($response);

		return $response;
	}

	public function getConsignmentsByDate(DateTimeInterface $date): ResponseInterface
	{
		$response = $this->client->getConsignment(null, $date);

		$this->assertResponse($response);

		return $response;
	}

	/**
	 * @param int[] $allowedStatusCodes
	 */
	protected function assertResponse(ResponseInterface $response, array $allowedStatusCodes = [200]): void
	{
		parent::assertResponse($response, $allowedStatusCodes);

		$content = $response->getBody()->getContents();
		$response->getBody()->rewind();

		if (substr($content, 0, 36) !== '<?xml version="1.0" encoding="UTF-8"') {
			throw new ResponseException($response, 'Response does not contain valid XML string');
		}

		try {
			$data = Helpers::xmlToArray($content);
		} catch (XmlException $e) {
			throw new ResponseException(
				$response,
				sprintf('Could not convert xml response. Error: %s', $e->getMessage())
			);
		}

		if (
			isset($data['chyby']) &&
			isset($data['chyby']['@attributes']) &&
			array_key_exists('stav', $data['chyby']['@attributes']) &&
			(int) $data['chyby']['@attributes']['stav'] !== 0
		) {
			throw new ResponseException(
				$response,
				sprintf('Response contains error code: %s', $data['chyby']['@attributes']['stav'])
			);
		}
	}

}
