<?php declare(strict_types = 1);

namespace Tests\Toolkit;

use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use Ninjify\Nunjuck\TestCase\BaseTestCase;

abstract class ContainerTestCase extends BaseTestCase
{

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
		// Create container
		$loader = new ContainerLoader(TEMP_DIR);
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
