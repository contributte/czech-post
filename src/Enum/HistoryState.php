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
		return $state === self::INVALID ||
			$state === self::NOT_FOUND ||
			$state === self::ANNOUNCED ||
			$state === self::ANNOUNCED ||
			$state === self::SUBMITTED ||
			$state === self::TRANSPORTED ||
			$state === self::ENTERING_DELIVERY_POST_OFFICE ||
			$state === self::REDIRECTING_TO_ANOTHER_ADDRESS ||
			$state === self::DAMAGED ||
			$state === self::STORED ||
			$state === self::BADLY_DIRECTED ||
			$state === self::OUT_OF_REGISTRY ||
			$state === self::DELIVERED_SUCCESSFULLY ||
			$state === self::RETURNED ||
			$state === self::DELIVERED_TO_SENDER;
	}

	public static function isErrorState(string $state): bool
	{
		return $state === self::INVALID ||
			$state === self::NOT_FOUND;
	}

	public static function isDeliveredSuccessfully(string $state): bool
	{
		return $state === self::DELIVERED_SUCCESSFULLY;
	}

}
