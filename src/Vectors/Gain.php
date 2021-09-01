<?php

namespace Fatty\Vectors;

use Fatty\Calculator;
use Fatty\Energy;

class Gain extends \Fatty\Vector
{
	const LABEL_INFINITIVE = "přibrat";
	const TDEE_QUOTIENT = 1.15;
	const WEIGHT_CHANGE_PER_WEEK = .3;

	public function getTdeeChangePerDay(Calculator $calculator)
	{
		return new Energy(new Amount($calculator->calcTotalDailyEnergyExpenditure()->getValue() * ($this->getTdeeQuotient($calculator)->getValue() - 1)), 'kCal');
	}
}
