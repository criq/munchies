<?php

namespace Fatty;

abstract class Metric
{
	protected $name;
	protected $result;
	protected $formula;

	abstract public function getResult();
	abstract public function getResponse(?Locale $locale = null): array;

	public function getName(): string
	{
		return $this->name;
	}

	public function getFormula(): ?string
	{
		return trim(preg_replace("/\s+/", " ", preg_replace("/[\t\n]/", " ", $this->formula)));
	}
}
