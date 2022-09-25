<?php

namespace Fatty\Exceptions;

use Katu\Errors\Error;
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

	public function getUnique(): FattyExceptionCollection
	{
		return (new static)->setExceptions(array_values(array_unique($this->getExceptions())));
	}

	public function getNames(): array
	{
		$res = [];
		foreach ($this as $exception) {
			$res = array_merge($res, $exception->getNames());
		}

		return array_values(array_unique($res));
	}

	public function getErrors(): ErrorCollection
	{
		$errors = new ErrorCollection;

		foreach ($this->getUnique()->getExceptions() as $exception) {
			$errors[] = new Error($exception->getMessage());
		}

		return $errors;
	}
}
