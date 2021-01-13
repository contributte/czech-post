<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Enum;

final class HistoryState
{

	// errors
	public const INVALID   = '-4'; // Pro tento druh zásilek Česká pošta informace nezobrazuje.
	public const NOT_FOUND = '-3'; // Zásilka tohoto podacího čísla není v evidenci.

	// common states
	public const ANNOUNCED                      = '-M'; // Obdrženy údaje k zásilce.
	public const SUBMITTED                      = '21'; // Podaná zásilka
	public const TRANSPORTED                    = '75'; // Přepravovaná zásilka (Pouze zásilky I.TU (DE))
	public const ENTERING_DELIVERY_POST_OFFICE  = '51'; // Vstup na dodací poštu
	public const REDIRECTING_TO_ANOTHER_ADDRESS = '8D'; // Dosílka na jinou adresu
	public const DAMAGED                        = '8E'; // Poškozená
	public const STORED                         = '82'; // Uložená
	public const BADLY_DIRECTED                 = '8T'; // Chybně směrovaná
	public const OUT_OF_REGISTRY                = '88'; // Vyšlá z evidence
	public const DELIVERED_SUCCESSFULLY         = '91'; // Doručená
	public const RETURNED                       = '95'; // Vrácená
	public const DELIVERED_TO_SENDER            = '9V'; // Doručená odesílateli

	public static function isKnownState(string $state): bool
	{
		return in_array($state, [
			self::INVALID,
			self::NOT_FOUND,
			self::ANNOUNCED,
			self::SUBMITTED,
			self::TRANSPORTED,
			self::ENTERING_DELIVERY_POST_OFFICE,
			self::REDIRECTING_TO_ANOTHER_ADDRESS,
			self::DAMAGED,
			self::STORED,
			self::BADLY_DIRECTED,
			self::OUT_OF_REGISTRY,
			self::DELIVERED_SUCCESSFULLY,
			self::RETURNED,
			self::DELIVERED_TO_SENDER,
		], true);
	}

	public static function isErrorState(string $state): bool
	{
		return in_array($state, [
			self::INVALID,
			self::NOT_FOUND,
		], true);
	}

	public static function isDeliveredSuccessfully(string $state): bool
	{
		return $state === self::DELIVERED_SUCCESSFULLY;
	}

}
