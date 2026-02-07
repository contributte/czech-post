<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\CpostRootquestor;
use Contributte\CzechPost\DI\CzechPostExtension;
use Contributte\CzechPost\Http\HttpClient;
use Contributte\CzechPost\Requestor\ConsignmentRequestor;
use Contributte\Tester\Utils\ContainerBuilder;
use GuzzleHttp\Client;
use Nette\DI\Compiler;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

class CzechPostClientExtensionTest extends TestCase
{

	public function testServicesRegistration(): void
	{
		$container = ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('contributte.czechpost', new CzechPostExtension());
			})->build();

		// Basic classes
		Assert::type(Client::class, $container->getService('contributte.czechpost.guzzle.client'));
		Assert::type(HttpClient::class, $container->getService('contributte.czechpost.http.client'));
		Assert::type(CpostRootquestor::class, $container->getService('contributte.czechpost.rootquestor'));

		// Clients
		Assert::type(ConsignmentClient::class, $container->getService('contributte.czechpost.client.consignment'));

		// Requestors
		Assert::type(ConsignmentRequestor::class, $container->getService('contributte.czechpost.requestor.consignment'));
		Assert::type(ConsignmentRequestor::class, $container->getService('contributte.czechpost.rootquestor')->consignment);
	}

	protected function setUpCompileContainer(Compiler $compiler): void
	{
		$compiler->addExtension('contributte.czechpost', new CzechPostExtension());
	}

}

(new CzechPostClientExtensionTest())->run();
