<?php

namespace Fatty;

use Fatty\Metrics\QuantityMetric;

abstract class Strategy
{
	abstract public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetric;
}
