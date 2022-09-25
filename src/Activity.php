<?php

namespace Fatty;

use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

class Activity extends Amount
{
	public static function validateActivity(Param $activity): Validation
	{
		$output = static::createFromString($activity);
		if (!$output) {
			return (new Validation)->addError((new Error("Invalida activity."))->addParam($activity));
		} else {
			return (new Validation)->addParam($activity->setOutput($output));
		}
	}
}
