<?php

namespace Fatty\Metrics;

use Fatty\Amount;
use Fatty\Locale;
use Fatty\Metric;

class AmountMetric extends Metric
{
	public function __construct(string $name, Amount $result, ?string $formula = null)
	{
		$this->name = $name;
		$this->result = $result;
		$this->formula = $formula;
	}

	public function getResult(): Amount
	{
		return $this->result;
	}

	public function getResponse(?Locale $locale = null): array
	{
		return [
			"name" => $this->getName(),
			"result" => $this->getResult()->getValue(),
			"formatted" => $this->getResult()->getFormatted($locale),
			"formula" => $this->getFormula(),
		];
	}
}
