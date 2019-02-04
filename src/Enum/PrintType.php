<?php declare(strict_types = 1);

namespace Contributte\CzechPost\Enum;

final class PrintType
{

	// Consignment print types
	public const ONE_SIDED = 0;
	public const TWO_SIDED = 1;
	public const BY_EACH_FILES_SETTINGS = 2;

	// Sender area printing options
	public const SENDER_USE_DETAILS = 1;
	public const SENDER_USE_FIRST_FILE = 2;
	public const SENDER_USE_CUSTOM_IMAGE = 3;

	// Recipient area printing options
	public const RECIPIENT_USE_DETAILS = 1;
	public const RECIPIENT_USE_FIRST_FILE = 2;

}
