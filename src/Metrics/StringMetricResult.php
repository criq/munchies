<?php

namespace Fatty\Metrics;

class StringMetricResult extends MetricResult
{
	public $formatted;

	public function getFormatted(): string
	{
		return $this->formatted ?: $this->result;
	}
}
