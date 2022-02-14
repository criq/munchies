<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountWithUnitMetric;

class LowEnergy extends \Fatty\Approach
{
	const CARBS_DEFAULT = 50;
	const CARBS_MAX = 50;
	const CARBS_MIN = 50;
	const CODE = "LOW_ENERGY";
	const ENERGY_DEFAULT = 800;
	const ENERGY_UNIT = "kcal";
	const FATS_DEFAULT = 30;
	const LABEL_DECLINATED = "nízkoenergetickou dietu";
	const PROTEINS_DEFAULT = 82;

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): AmountWithUnitMetric
	{
		if (!$calculator->getGoal()->getVector()) {
			throw new \Fatty\Exceptions\MissingGoalVectorException;
		}

		$result = (new Energy(
			new Amount(static::ENERGY_DEFAULT),
			static::ENERGY_UNIT,
		))->getInUnit($calculator->getUnits());

		return new AmountWithUnitMetric("weightGoalEnergyExpenditure", $result);
	}
}
