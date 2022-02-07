<?php

namespace Fatty\Vectors;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\AmountWithUnitMetric;

class Maintain extends \Fatty\Vector
{
	const LABEL_INFINITIVE = "udržovat hmotnost";
	const TDEE_QUOTIENT = 1.05;
	const TDEE_QUOTIENT__LARGE = 1.07;
	const WEIGHT_CHANGE_PER_WEEK = 0;

	public function calcTdeeQuotient(Calculator $calculator): AmountMetric
	{
		$result = new Amount(
			$calculator->calcPhysicalActivityLevel()->getResult()->getValue() >= 2 ? static::TDEE_QUOTIENT__LARGE : static::TDEE_QUOTIENT
		);

		return new AmountMetric("tdeeQuotient", $result);
	}

	public function calcTdeeChangePerDay(Calculator $calculator): AmountWithUnitMetric
	{
		return new AmountWithUnitMetric("tdeeChangePerDay", new Energy(new Amount(0), Energy::getBaseUnit()));
	}

	public function calcTotalDailyEnergyExpenditure(Calculator $calculator): AmountWithUnitMetric
	{
		return $calculator->calcTotalDailyEnergyExpenditure();
	}
}
