<?php

namespace Fatty;

class BreastfeedingMode
{
	const CODE = "";

	public function getCode(): string
	{
		return static::CODE;
	}

	public static function getAvailable(): array
	{
		return [
			BreastfeedingModes\None::CODE => new BreastfeedingModes\None,
			BreastfeedingModes\Partial::CODE => new BreastfeedingModes\Partial,
			BreastfeedingModes\Full::CODE => new BreastfeedingModes\Full,
		];
	}
}
