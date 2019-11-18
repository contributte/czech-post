<?php declare(strict_types = 1);

namespace Tests\Toolkit\Contributte\CzechPost;

use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use PHPUnit\Framework\TestCase;

abstract class ContainerTestCase extends TestCase
{

	protected const TEMP_DIR = __DIR__ . '/../tmp';

	/** @var Container */
	protected $container;

	/** @var mixed[] */
	protected $coptions = [
		'unique' => false,
		'classUnique' => false,
		'parameters' => [],
	];

	protected function setUp(): void
	{
		parent::setUp();
		$this->container = $this->getContainer();
	}

	protected function getContainer(): Container
	{
		if (!$this->container) {
			$this->container = $this->createContainer();
			$this->setUpContainer($this->container);
		}

		return $this->container;
	}

	protected function createContainer(): Container
	{
		// Check composer && tester
		if (@!include __DIR__ . '/../../vendor/autoload.php') {
			echo 'Install PhpUnit using `composer update --dev`';
			exit(1);
		}

		// Create container
		$loader = new ContainerLoader(self::TEMP_DIR);
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
		return new $class();
	}

	protected function setUpContainer(Container $container): void
	{
	}

	protected function setUpCompileContainer(Compiler $compiler): void
	{
	}

}
