<?php

namespace Fatty\Vectors;

use Fatty\Calculator;

class SlowLoose extends Loose
{
	const LABEL_INFINITIVE = "pomalu zhubnout";
	const WEIGHT_CHANGE_PER_WEEK = -.5;

	public function calcTdeeChangePerDay(Calculator $calculator)
	{
		return new \Fatty\Energy(($calculator->calcTotalDailyEnergyExpenditure()->getAmount() - $calculator->calcBasalMetabolicRate()->getAmount()) * -.35, 'kCal');
	}
}
