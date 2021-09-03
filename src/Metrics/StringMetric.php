<?php

namespace Fatty\Metrics;

use Fatty\Metric;

class StringMetric extends Metric
{
	public function __construct(string $name, string $result, ?string $formatted = null, ?string $formula = null)
	{
		$this->name = $name;
		$this->result = $result;
		$this->formatted = $formatted;
		$this->formula = $formula;
	}

	public function getResult(): string
	{
		return $this->result;
	}

	public function getFormatted(): string
	{
		return $this->formatted ?: $this->result;
	}

	public function getResponse(): array
	{
		return [
			'name' => $this->getName(),
			'result' => $this->getResult(),
			'formatted' => $this->getFormatted(),
			'formula' => $this->getFormula(),
		];
	}
}
