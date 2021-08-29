<?php

namespace Fatty\WeightVectors;

class Gain extends \Fatty\WeightVector
{
	const LABEL_INFINITIVE = "přibrat";
	const TDEE_QUOTIENT = 1.15;
	const WEIGHT_CHANGE_PER_WEEK = .3;

	public function getTdeeChangePerDay(&$calculator)
	{
		return new \Fatty\Energy($calculator->getTotalDailyEnergyExpenditure()->getAmount() * ($this->getTdeeQuotient($calculator) - 1), 'kCal');
	}
}
