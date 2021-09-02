<?php

namespace Fatty\Exceptions;

class MissingHeightException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící výška.";
		$this->names = ['proportions_height'];
	}
}
