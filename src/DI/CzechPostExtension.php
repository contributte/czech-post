<?php declare(strict_types = 1);

namespace Contributte\CzechPost\DI;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\CpostRootquestor;
use Contributte\CzechPost\Http\GuzzleClient;
use Contributte\CzechPost\Http\HttpClient;
use Contributte\CzechPost\Requestor\ConsignmentRequestor;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Nette\DI\CompilerExtension;

class CzechPostExtension extends CompilerExtension
{

	/** @var mixed[] */
	protected $defaults = [
		'http' => [
			'base_uri' => null,
			'auth' => [],
		],
		'config' => [],
	];

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		// #1 HTTP client
		$builder->addDefinition($this->prefix('guzzle.client'))
			->setFactory(Client::class, [$config])
			->setType(ClientInterface::class)
			->setAutowired(false);

		$builder->addDefinition($this->prefix('http.client'))
			->setFactory(GuzzleClient::class, [$this->prefix('@guzzle.client')])
			->setType(HttpClient::class)
			->setAutowired(false);

		// #2 Clients
		$builder->addDefinition($this->prefix('client.consignment'))
			->setFactory(ConsignmentClient::class, [$this->prefix('@http.client'), $config]);

		// #3 Requestors
		$builder->addDefinition($this->prefix('requestor.consignment'))
			->setFactory(ConsignmentRequestor::class, [$this->prefix('@client.consignment')]);

		// #4 Rootquestor
		$builder->addDefinition($this->prefix('rootquestor'))
			->setFactory(CpostRootquestor::class);

		// #4 -> #3 connect rootquestor to requestors
		$builder->getDefinition($this->prefix('rootquestor'))
			->addSetup('add', ['consignment', $this->prefix('@requestor.consignment')]);
	}

}
