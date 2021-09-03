<?php

namespace Fatty\Metrics;

use Fatty\AmountWithUnit;
use Fatty\Locale;
use Fatty\Metric;

class AmountWithUnitMetric extends Metric
{
	public function __construct(string $name, AmountWithUnit $result, ?string $formula = null)
	{
		$this->name = $name;
		$this->result = $result;
		$this->formula = $formula;
	}

	public function getResult(): AmountWithUnit
	{
		return $this->result;
	}

	public function getResponse(?Locale $locale = null): array
	{
		return [
			'name' => $this->getName(),
			'result' => $this->getResult()->getAmount()->getValue(),
			'formatted' => $this->getResult()->getFormatted($locale),
			'formula' => $this->getFormula(),
		];
	}
}
