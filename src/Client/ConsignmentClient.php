<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Client;

use Contributte\CzechPost\Entity\Consignment;
use Contributte\CzechPost\Exception\LogicalException;
use Contributte\CzechPost\Http\AbstractCpostHttpClient;
use Contributte\CzechPost\Http\HttpClient;
use Contributte\CzechPost\XmlRequest\ConsignmentRequestFactory;
use DateTimeInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class ConsignmentClient extends AbstractCpostHttpClient
{

	private const PATH_CONSIGNMENT_SEND = 'donApi.php';

	private const PATH_CONSIGNMENT_DETAIL = 'donPrehledZak.php';

	private const PATH_CONSIGNMENT_LABEL = 'podlist.php';

	/** @var ConsignmentRequestFactory */
	private $requestFactory;

	/**
	 * @param mixed[] $config
	 */
	public function __construct(HttpClient $httpClient, array $config)
	{
		parent::__construct($httpClient, $config);

		$this->requestFactory = new ConsignmentRequestFactory();
	}

	public function sendConsignment(Consignment $consignment): ResponseInterface
	{
		$tmpFile = $this->getTmpDir() . bin2hex(random_bytes(12)) . '.xml';
		$xml = $this->requestFactory->create($consignment);
		$this->createTmpFile($tmpFile, $xml->saveXML());

		$options = array_merge(
			$this->getCommonRequestOptions(),
			[
				'multipart' => [
					[
						'name' => 'user',
						'contents' => $this->getUsername(),
					],
					[
						'name' => 'password',
						'contents' => $this->getPassword(),
					],
					[
						'name' => 'soubor',
						'contents' => fopen($tmpFile, 'r'),
					],
				],
				'defaults' => [
					'headers' => [
						'Content-Type' => 'multipart/form-data',
					],
				],
			]
		);

		$response = $this->httpClient->request('POST', self::PATH_CONSIGNMENT_SEND, $options);
		unlink($tmpFile);

		return $response;
	}

	public function getConsignment(?string $id = null, ?DateTimeInterface $date = null): ResponseInterface
	{
		if ($id === null && $date === null) {
			throw new InvalidArgumentException('You must provide consignmentId and/or date params');
		}

		$dateString = '';
		if ($date !== null) {
			$dateString = $date->format('Ymd');
		}

		$options = array_merge(
			$this->getCommonRequestOptions(),
			[
				'form_params' => [
					'user' => $this->getUsername(),
					'password' => $this->getPassword(),
					'zasilka' => $id ?? '',
					'datum' => $id !== null ? '' : $dateString,
				],
				'defaults' => [
					'headers' => [
						'Content-Type' => 'application/x-www-form-urlencoded',
					],
				],
			]
		);

		return $this->httpClient->request('POST', self::PATH_CONSIGNMENT_DETAIL, $options);
	}

	public function getConfirmationLabel(string $trackingNumber): ResponseInterface
	{
		$options = array_merge(
			$this->getCommonRequestOptions(),
			[
				'form_params' => [
					'user' => $this->getUsername(),
					'password' => $this->getPassword(),
					'podcislo' => $trackingNumber,
					'typvystupu' => 'D',
				],
				'defaults' => [
					'headers' => [
						'Content-Type' => 'application/x-www-form-urlencoded',
					],
				],
			]
		);

		return $this->httpClient->request('POST', self::PATH_CONSIGNMENT_LABEL, $options);
	}

	private function createTmpFile(string $path, string $content): void
	{
		$fh = fopen($path, 'w');

		if ($fh === false) {
			throw new LogicalException(sprintf('Cannot create temp file %s', $path));
		}

		fwrite($fh, $content);
		fclose($fh);
	}

	/**
	 * @return mixed[]
	 */
	private function getCommonRequestOptions(): array
	{
		return [
			'timeout' => self::REQUEST_TIMEOUT,
			'verify' => array_key_exists('ssl_key', $this->config),
		];
	}

}
