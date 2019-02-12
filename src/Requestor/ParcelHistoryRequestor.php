<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Requestor;

use Contributte\CzechPost\Client\ParcelHistoryClient;
use Contributte\CzechPost\Entity\State;
use Contributte\CzechPost\Exception\LogicalException;
use Contributte\CzechPost\Exception\Runtime\ResponseException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\ResponseInterface;

final class ParcelHistoryRequestor extends AbstractRequestor
{

	/** @var ParcelHistoryClient */
	protected $client;

	public function __construct(ParcelHistoryClient $client)
	{
		$this->client = $client;
	}

	public function status(string $trackingNumber): State
	{
		$states = $this->history($trackingNumber);

		$last = end($states);
		if ($last === false) {
			throw new LogicalException('No parcel state');
		}

		return $last;
	}

	/**
	 * @return State[]
	 */
	public function history(string $trackingNumber): array
	{
		$resp = $this->client->history($trackingNumber);
		$rawStates = $this->parseStates($resp);

		$states = [];
		foreach ($rawStates as $rs) {
			$states[] = State::fromArray((array) $rs);
		}

		return $states;
	}

	/**
	 * @return mixed[]
	 */
	private function parseStates(ResponseInterface $response): array
	{
		if ($response->getStatusCode() !== 200) {
			throw new ResponseException(
				$response,
				sprintf('Server responded with status code "%d"', $response->getStatusCode())
			);
		}

		try {
			$data = Json::decode($response->getBody()->getContents());
		} catch (JsonException $e) {
			throw new ResponseException($response, 'Cannot decode response json');
		}

		if (!isset($data[0]) ||
			!isset($data[0]->states) ||
			!isset($data[0]->states->state) ||
			!is_array($data[0]->states->state) ||
			!array_key_exists(0, $data[0]->states->state)) {
			throw new ResponseException($response, 'Response does not contain any parcel state');
		}

		$firstState = $data[0]->states->state[0];
		if (!isset($firstState->id) || (int) $firstState->id < 0) {
			throw new ResponseException($response, sprintf('Tracking error "%s"', $firstState->text));
		}

		return $data[0]->states->state;
	}

}
