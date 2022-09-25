<?php

namespace Fatty\Exceptions;

class MissingNeckException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící obvod krku.";
		$this->paramKeys = ['proportions_neck'];
	}
}
