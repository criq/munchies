<?php

namespace Fatty\Exceptions;

class MissingBodyFatPercentageInputException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybí míry k výpočtu procenta tělesného tuku, nebo jeho přímé zadání.";
		$this->paramKeys = [
			"bodyFatPercentage",
			"proportions_height",
			"proportions_hips",
			"proportions_neck",
			"proportions_waist",
		];
	}
}
