<?php

namespace Fatty;

abstract class Metric
{
	protected $name;
	protected $result;
	protected $formula;

	abstract public function getResult();
	abstract public function getResponse(): array;

	public function getName(): string
	{
		return $this->name;
	}

	public function getFormula(): ?string
	{
		return $this->formula;
	}
}
