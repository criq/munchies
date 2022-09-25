<?php

namespace Fatty\Exceptions;

use Katu\Errors\ErrorCollection;
use Katu\Types\TClass;

class FattyExceptionCollection extends \Fatty\Exceptions\FattyException
{
	protected $exceptions = [];

	public function addException(FattyException $exception): FattyExceptionCollection
	{
		if ($exception instanceof static) {
			foreach ($exception->getExceptions() as $e) {
				$this->addException($e);
			}
		} else {
			$this->exceptions[(new TClass($exception))->getPortableName()] = $exception;
		}

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
