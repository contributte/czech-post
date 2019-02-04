<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Requestor;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\Entity\Consignment;
use Contributte\CzechPost\Entity\Dispatch;
use Contributte\CzechPost\Exception\Logical\XmlException;
use Contributte\CzechPost\Exception\Runtime\ResponseException;
use Contributte\CzechPost\Utils\Helpers;
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

	public function sendConsignment(Consignment $consignment): Dispatch
	{
		$response = $this->client->sendConsignment($consignment);
		$data = $this->validateXmlResponse($response);

		return Dispatch::fromArray($data);
	}

	public function getConsignmentOverview(string $id): Dispatch
	{
		$response = $this->client->getConsignment($id);
		$data = $this->validateXmlResponse($response);

		if (!isset($data['zakazka'])) {
			throw new ResponseException($response, sprintf('No "zakazka" found for given id: %s', $id));
		}

		return Dispatch::fromArray($data['zakazka']);
	}

	/**
	 * @return Dispatch[]
	 */
	public function getConsignmentsByDate(DateTimeInterface $date): array
	{
		$response = $this->client->getConsignment(null, $date);
		$data = $this->validateXmlResponse($response);

		if (!isset($data['zakazka'])) {
			throw new ResponseException($response, sprintf('No "zakazka" found for given date: %s', $date->format('Y-m-d')));
		}

		$dis = [];
		foreach ($data['zakazka'] as $order) {
			$dis[] = Dispatch::fromArray($order);
		}

		return $dis;
	}

	public function getConsignmentLabel(string $trackingNumber): string
	{
		$response = $this->client->getConfirmationLabel($trackingNumber);
		parent::assertResponse($response, [200]);

		return $response->getBody()->getContents();
	}

	/**
	 * @param int[] $allowedStatusCodes
	 * @return mixed[]
	 */
	protected function validateXmlResponse(ResponseInterface $response, array $allowedStatusCodes = [200]): array
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

			// xml error node is present
		if (isset($data['kod']) && isset($data['popis'])) {
			throw new ResponseException(
				$response,
				sprintf('Error: %s, Code: %s', (string) $data['popis'], (string) $data['kod'])
			);
		}

		return $data;
	}

}
