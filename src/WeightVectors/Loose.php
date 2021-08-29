<?php

namespace Fatty\WeightVectors;

class Loose extends \Fatty\WeightVector
{
	const LABEL_INFINITIVE = "zhubnout";
	const TDEE_QUOTIENT = 1;
	const WEIGHT_CHANGE_PER_WEEK = -.8;

	public function getTdeeChangePerDay(&$calculator)
	{
		return new \Fatty\Energy(($calculator->getTotalDailyEnergyExpenditure()->getAmount() - ($calculator->getBasalMetabolicRate()->getAmount() + 50)) * -1, 'kCal');
	}
}
