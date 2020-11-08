<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Http;

use InvalidArgumentException;

abstract class AbstractCpostHttpClient
{

	protected const REQUEST_TIMEOUT = 5;

	/** @var mixed[] */
	protected $config = [];

	/** @var HttpClient */
	protected $httpClient;

	/**
	 * @param mixed[] $config
	 */
	public function __construct(HttpClient $httpClient, array $config)
	{
		$this->httpClient = $httpClient;
		$this->config = $config;
	}

	protected function getUsername(): string
	{
		$this->assertUsernamePassword();

		return $this->config['http']->auth[0];
	}

	protected function getPassword(): string
	{
		$this->assertUsernamePassword();

		return $this->config['http']->auth[1];
	}

	protected function getTmpDir(): string
	{
		if (!isset($this->config->config) || !isset($this->config->config->tmp_dir)) {
			return sys_get_temp_dir();
		}

		return $this->config->config->tmp_dir;
	}

	protected function assertUsernamePassword(): void
	{
		if (!isset($this->config['http']) || !isset($this->config['http']->auth)) {
			throw new InvalidArgumentException('Mandatory "auth" section of Cpost client configuration is missing.');
		}

		if (!isset($this->config['http']->auth[0]) || strlen($this->config['http']->auth[0]) === 0) {
			throw new InvalidArgumentException('You must provide valid auth Cpost client username');
		}

		if (!isset($this->config['http']->auth[1]) || strlen($this->config['http']->auth[1]) === 0) {
			throw new InvalidArgumentException('You must provide valid auth Cpost client password');
		}
	}

}
