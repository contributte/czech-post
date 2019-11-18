<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Utils;

use Contributte\CzechPost\Exception\Logical\XmlException;
use Contributte\CzechPost\Exception\RuntimeException;
use LibXMLError;
use SimpleXMLElement;

final class Helpers
{

	public static function strToXml(string $data): SimpleXMLElement
	{
		$xml = simplexml_load_string($data);

		if ($xml === false) {
			/** @var LibXMLError $error */
			foreach (libxml_get_errors() as $error) {
				throw new RuntimeException(sprintf('Error parsing XML string: %s', $error->message));
			}

			throw new RuntimeException(sprintf('Error parsing XML response.'));
		}

		return $xml;
	}

	/**
	 * @return mixed[]
	 */
	public static function xmlToArray(string $xml): array
	{
		libxml_use_internal_errors(true);
		$parsed = simplexml_load_string($xml);

		if ($parsed === false) {
			/** @var LibXMLError $error */
			foreach (libxml_get_errors() as $error) {
				throw new XmlException(sprintf('Could not parse xml string. Error: %s', $error->message));
			}

			libxml_clear_errors();
		}

		$encoded = json_encode($parsed);
		if ($encoded === false) {
			throw new XmlException('Could not encode to json.');
		}

		return json_decode($encoded, true);
	}

}
