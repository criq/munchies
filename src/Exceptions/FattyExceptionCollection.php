<?php

namespace Fatty\Exceptions;

use Katu\Errors\ErrorCollection;

class FattyExceptionCollection extends \Fatty\Exceptions\FattyException
{
	protected $exceptions = [];

	public function addException(FattyException $exception): FattyExceptionCollection
	{
		$this->exceptions[] = $exception;

		return $this;
	}

	public function setExceptions(array $exceptions): FattyExceptionCollection
	{
		$this->exceptions = $exceptions;

		return $this;
	}

	public function getExceptions(): array
	{
		return $this->exceptions;
	}

	public function hasExceptions(): bool
	{
		return (bool)count($this->getExceptions());
	}

	public function getErrors(): ErrorCollection
	{
		$errors = new ErrorCollection;

		foreach ($this->getExceptions() as $exception) {
			$errors[] = $exception->getError();
		}

		return $errors;
	}
}
