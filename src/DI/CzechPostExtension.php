<?php declare(strict_types = 1);

namespace Contributte\CzechPost\DI;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\Client\ParcelHistoryClient;
use Contributte\CzechPost\CpostRootquestor;
use Contributte\CzechPost\Http\GuzzleClient;
use Contributte\CzechPost\Http\HttpClient;
use Contributte\CzechPost\Requestor\ConsignmentRequestor;
use Contributte\CzechPost\Requestor\ParcelHistoryRequestor;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class CzechPostExtension extends CompilerExtension
{

	/** @var mixed[] */
	protected array $defaults = [
		'http' => [
			'base_uri' => 'https://online.postservis.cz/',
			'auth' => ['', ''],
		],
		'config' => [
			'tmp_dir' => null,
		],
	];

	public function getConfigSchema(): Schema
	{
		return Expect::structure(
			[
				'http' => Expect::structure([
					'base_uri' => Expect::string('https://online.postservis.cz/'),
					'auth' => Expect::array(['', '']),
				]),
				'config' => Expect::structure([
					 'tmp_dir' => Expect::type('string|null')->default(null),
				]),

			]
		);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = (array) $this->getConfig();

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

		$builder->addDefinition($this->prefix('client.history'))
			->setFactory(ParcelHistoryClient::class, [$this->prefix('@http.client'), $config]);

		// #3 Requestors
		$builder->addDefinition($this->prefix('requestor.consignment'))
			->setFactory(ConsignmentRequestor::class, [$this->prefix('@client.consignment')]);

		$builder->addDefinition($this->prefix('requestor.history'))
			->setFactory(ParcelHistoryRequestor::class, [$this->prefix('@client.history')]);

		// #4 Rootquestor
		$builder->addDefinition($this->prefix('rootquestor'))
			->setFactory(CpostRootquestor::class)
			->addSetup('add', ['consignment', $this->prefix('@requestor.consignment')])
			->addSetup('add', ['history', $this->prefix('@requestor.history')]);
	}

}
