<?php

namespace Fatty\Metrics;

use Fatty\Amount;
use Fatty\Metric;

class AmountMetric extends Metric
{
	public function __construct(string $name, Amount $result, ?string $formula = null)
	{
		$this->name = $name;
		$this->result = $result;
		$this->formula = $formula;
	}

	public function getResult() : Amount
	{
		return $this->result;
	}

	public function getResponse() : array
	{
		return [
			'name' => $this->getName(),
			'result' => $this->getResult()->getValue(),
			'formatted' => $this->getResult()->getFormatted(),
			'formula' => $this->getFormula(),
		];
	}
}
