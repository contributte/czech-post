<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\CzechPost\Client\ConsignmentClient;
use Contributte\CzechPost\CpostRootquestor;
use Contributte\CzechPost\DI\CzechPostExtension;
use Contributte\CzechPost\Http\HttpClient;
use Contributte\CzechPost\Requestor\ConsignmentRequestor;
use GuzzleHttp\Client;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use Tester\Assert;
use Tester\TestCase;
use Tests\Toolkit\Tests;

require __DIR__ . '/../../bootstrap.php';

class CzechPostClientExtensionTest extends TestCase
{

	/** @var Container */
	protected $container;

	/** @var mixed[] */
	protected $coptions = [
		'unique' => false,
		'classUnique' => false,
		'parameters' => [],
	];

	public function setUp(): void
	{
		$this->container = $this->createContainer();
		parent::setUp();
	}

	public function setUpCompileContainer(Compiler $compiler): void
	{
		$compiler->addExtension('contributte.czechpost', new CzechPostExtension());
	}

	public function createContainer(): Container
	{
		// Check composer && tester
		if (@!include __DIR__ . '/../../../vendor/autoload.php') {
			echo 'Install Nette Tester using `composer update --dev`';
			exit(1);
		}

		// Create container
		$loader = new ContainerLoader(Tests::TEMP_PATH);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('extensions', new ExtensionsExtension());

			// Customizations
			if ($this->coptions['unique'] === true) {
				$compiler->addConfig([
					'parameters' => [
						'tests__timestamp' => microtime(true),
					],
				]);
			}

			if ($this->coptions['classUnique'] === true) {
				$compiler->addConfig([
					'parameters' => [
						'tests__called_class' => static::class,
					],
				]);
			}

			if ($this->coptions['parameters'] === true) {
				$compiler->addConfig([
					'parameters' => $this->coptions['parameters'],
				]);
			}

			// Call decorated method
			$this->setUpCompileContainer($compiler);
		});

		// Create test container
		$this->container = new $class();

		return $this->container;
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
