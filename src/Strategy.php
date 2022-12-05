<?php

namespace Fatty;

use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;

abstract class Strategy
{
	abstract public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetric;
	abstract public function calcWeightGoalQuotient(Calculator $calculator): AmountMetric;
}
