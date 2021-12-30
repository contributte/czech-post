<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\CpostRootquestor;
use Contributte\CzechPost\DI\CzechPostExtension;
use Contributte\CzechPost\Http\HttpClient;
use Contributte\CzechPost\Requestor\ConsignmentRequestor;
use GuzzleHttp\Client;
use Nette\DI\Compiler;
use Tester\Assert;
use Tests\Toolkit\ContainerTestCase;

require_once __DIR__ . '/../../bootstrap.php';

class CzechPostClientExtensionTest extends ContainerTestCase
{

	protected function setUpCompileContainer(Compiler $compiler): void
	{
		$compiler->addExtension('contributte.czechpost', new CzechPostExtension());
	}

	public function testServicesRegistration(): void
	{
		// Basic classes
		Assert::type(Client::class, $this->container->getService('contributte.czechpost.guzzle.client'));
		Assert::type(HttpClient::class, $this->container->getService('contributte.czechpost.http.client'));
		Assert::type(CpostRootquestor::class, $this->container->getService('contributte.czechpost.rootquestor'));

		// Clients
		Assert::type(ConsignmentClient::class, $this->container->getService('contributte.czechpost.client.consignment'));

		// Requestors
		Assert::type(ConsignmentRequestor::class, $this->container->getService('contributte.czechpost.requestor.consignment'));
		Assert::type(ConsignmentRequestor::class, $this->container->getService('contributte.czechpost.rootquestor')->consignment);
	}

}

(new CzechPostClientExtensionTest())->run();
