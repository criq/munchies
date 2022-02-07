<?php

namespace Fatty\Vectors;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Metrics\AmountWithUnitMetric;

class SlowLoose extends Loose
{
	const LABEL_INFINITIVE = "pomalu zhubnout";
	const WEIGHT_CHANGE_PER_WEEK = -.5;

	public function calcTdeeChangePerDay(Calculator $calculator): AmountWithUnitMetric
	{
		$totalDailyEnergyExpenditureValue = $calculator->calcTotalDailyEnergyExpenditure()->getResult()->getInUnit("kCal")->getAmount()->getValue();
		$basalMetabolicRateValue = $calculator->calcBasalMetabolicRate()->getResult()->getInUnit("kCal")->getAmount()->getValue();

		$result = new \Fatty\Energy(
			new Amount(
				($totalDailyEnergyExpenditureValue - $basalMetabolicRateValue) * -.35
			),
			"kCal",
		);

		return new AmountWithUnitMetric("tdeeChangePerDay", $result);
	}
}
