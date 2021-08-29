<?php

namespace Fatty\Vectors;

use Fatty\Calculator;

class Loose extends \Fatty\Vector
{
	const LABEL_INFINITIVE = "zhubnout";
	const TDEE_QUOTIENT = 1;
	const WEIGHT_CHANGE_PER_WEEK = -.8;

	public function getTdeeChangePerDay(Calculator $calculator)
	{
		return new \Fatty\Energy(($calculator->getTotalDailyEnergyExpenditure()->getAmount() - ($calculator->getBasalMetabolicRate()->getAmount() + 50)) * -1, 'kCal');
	}
}
