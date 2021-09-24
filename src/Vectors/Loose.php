<?php

namespace Fatty\Vectors;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountWithUnitMetric;

class Loose extends \Fatty\Vector
{
	const LABEL_INFINITIVE = "zhubnout";
	const TDEE_QUOTIENT = 1;
	const WEIGHT_CHANGE_PER_WEEK = -.8;

	public function calcTdeeChangePerDay(Calculator $calculator): AmountWithUnitMetric
	{
		$totalDailyEnergyExpenditureValue = $calculator->calcTotalDailyEnergyExpenditure()->getResult()->getInUnit('kCal')->getAmount()->getValue();
		$basalMetabolicRateValue = $calculator->calcBasalMetabolicRate()->getResult()->getInUnit('kCal')->getAmount()->getValue();

		$result = new Energy(
			new Amount(
				($totalDailyEnergyExpenditureValue - ($basalMetabolicRateValue + 50)) * -1
			),
			'kCal',
		);

		return new AmountWithUnitMetric('tdeeChangePerDay', $result);
	}
}
