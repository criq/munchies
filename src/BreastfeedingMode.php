<?php

namespace Fatty;

use Katu\Tools\Validation\Params\UserInput;
use Katu\Tools\Validation\Validation;

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

	public static function validate(UserInput $param): Validation
	{
		$validation = new Validation;

		$output = trim($param);
		if (!mb_strlen($output)) {
			$validation->addError((new Error("Chybějící způsob kojení."))->addParam($param));
		} else {
			$output = static::getAvailable()[$output] ?? null;
			if (!$output) {
				$validation->addError((new Error("Neplatný způsob kojení."))->addParam($param));
			} else {
				$validation->setResponse($output)->addParam($param->setOutput($output));
			}
		}

		return $validation;
	}
}
