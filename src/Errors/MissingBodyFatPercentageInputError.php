<?php

namespace Fatty\Errors;

use Fatty\Error;
use Katu\Tools\Validation\ParamCollection;

class MissingBodyFatPercentageInputError extends Error
{
	const MESSAGE = "Chybí míry k výpočtu procenta tělesného tuku, nebo jeho přímé zadání.";

	public function getParams(): ParamCollection
	{
		return (new ParamCollection)->getWithKeys([
			"bodyFatPercentage",
			"proportions_height",
			"proportions_hips",
			"proportions_neck",
			"proportions_waist",
		]);
	}
}
