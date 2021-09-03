<?php

namespace Fatty;

class Locale
{
	protected $value;

	public function __construct(string $value)
	{
		$this->setValue($value);
	}

	public function __toString(): string
	{
		return $this->getValue();
	}

	public function setValue(string $value): Locale
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public static function getDefault(): Locale
	{
		return new static('cs_CZ');
	}
}
