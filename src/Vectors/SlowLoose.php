<?php

namespace Fatty\Vectors;

use Fatty\Calculator;

class SlowLoose extends Loose
{
	const LABEL_INFINITIVE = "pomalu zhubnout";
	const WEIGHT_CHANGE_PER_WEEK = -.5;

	public function getTdeeChangePerDay(Calculator $calculator)
	{
		return new \Fatty\Energy(($calculator->getTotalDailyEnergyExpenditure()->getAmount() - $calculator->getBasalMetabolicRate()->getAmount()) * -.35, 'kCal');
	}
}
