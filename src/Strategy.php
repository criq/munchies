<?php

namespace Fatty;

use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\QuantityMetricResult;

abstract class Strategy
{
	abstract public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult;
	abstract public function calcWeightGoalQuotient(Calculator $calculator): AmountMetricResult;
}
