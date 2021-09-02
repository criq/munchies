<?php

namespace Fatty\Exceptions;

class InvalidPregnancyChildbirthDateException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatné datum narození dítěte.";
	}
}
