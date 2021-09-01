<?php

namespace Fatty\Vectors;

use Fatty\Calculator;

class Gain extends \Fatty\Vector
{
	const LABEL_INFINITIVE = "přibrat";
	const TDEE_QUOTIENT = 1.15;
	const WEIGHT_CHANGE_PER_WEEK = .3;

	public function getTdeeChangePerDay(Calculator $calculator)
	{
		return new \Fatty\Energy($calculator->calcTotalDailyEnergyExpenditure()->getAmount() * ($this->getTdeeQuotient($calculator)->getValue() - 1), 'kCal');
	}
}
