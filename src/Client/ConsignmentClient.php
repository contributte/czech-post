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

	private const PATH_SEND = 'donApi.php';
	private const PATH_DETAIL = 'donPrehledZak.php';
	private const PATH_LABEL = 'podlist.php';
	private const PATH_ENUMS = 'vratCiselnik.php';
	private const PATH_CANCEL = 'donStorno.php';

	private ConsignmentRequestFactory $requestFactory;

	/**
	 * @param mixed[] $config
	 */
	public function __construct(HttpClient $httpClient, array $config)
	{
		parent::__construct($httpClient, $config);

		$this->requestFactory = new ConsignmentRequestFactory();
	}

	public function send(Consignment $consignment): ResponseInterface
	{
		$tmpFile = tempnam($this->getTmpDir(), 'CZPost') . '.xml';

		$xml = $this->requestFactory->create($consignment);
		$xmldata = $xml->saveXML();

		if ($xmldata === false) {
			throw new LogicalException('Cannot create XML data');
		}

		$this->createTmpFile($tmpFile, $xmldata);

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

		$response = $this->httpClient->request('POST', $this->config['http']['base_uri'] . self::PATH_SEND, $options);
		unlink($tmpFile);

		return $response;
	}

	public function find(?string $id = null, ?DateTimeInterface $date = null): ResponseInterface
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

		return $this->httpClient->request('POST', $this->config['http']['base_uri'] . self::PATH_DETAIL, $options);
	}

	public function printLabel(string $trackingNumber): ResponseInterface
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

		return $this->httpClient->request('POST', $this->config['http']['base_uri'] . self::PATH_LABEL, $options);
	}

	public function cancel(?string $id = null): ResponseInterface
	{
		$options = array_merge(
			$this->getCommonRequestOptions(),
			[
				'form_params' => [
					'user' => $this->getUsername(),
					'password' => $this->getPassword(),
					'typ' => $id === null ? '0' : '1',
				],
				'defaults' => [
					'headers' => [
						'Content-Type' => 'application/x-www-form-urlencoded',
					],
				],
			]
		);

		if ($id !== null) {
			$options['form_params']['zasilka'] = $id;
		}

		return $this->httpClient->request('POST', $this->config['http']['base_uri'] . self::PATH_CANCEL, $options);
	}

	public function fetchEnum(bool $payoffType, bool $paymentType, bool $iso): ResponseInterface
	{
		$options = array_merge(
			$this->getCommonRequestOptions(),
			[
				'form_params' => [
					'user' => $this->getUsername(),
					'password' => $this->getPassword(),
				],
				'defaults' => [
					'headers' => [
						'Content-Type' => 'application/x-www-form-urlencoded',
					],
				],
			]
		);
		$type = null;

		if ($payoffType === true) {
			$type = 'typvyplatneho';
		}

		if ($paymentType === true) {
			$type = 'typuhrady';
		}

		if ($iso === true) {
			$type = 'iso';
		}

		if ($type === null) {
			throw new LogicalException('Type of enum to fetch not specified.');
		}

		$options['form_params']['typciselniku'] = $type;

		return $this->httpClient->request('POST', $this->config['http']['base_uri'] . self::PATH_ENUMS, $options);
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
