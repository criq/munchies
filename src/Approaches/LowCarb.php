<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Nutrients;
use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;

class LowCarb extends \Fatty\Approach
{
	const CARBS_DEFAULT = 90;
	const CARBS_MAX = 90;
	const CARBS_MIN = 50;
	const CODE = "LOW_CARB";
	const LABEL_DECLINATED = "nízkosacharidovou dietu";

	public function getGoalNutrients(Calculator $calculator): Nutrients
	{
		$nutrients = new Nutrients;
		$nutrients->setProteins($this->calcGoalNutrientsProteins($calculator)->getResult());

		$wgee = $calculator->calcWeightGoalEnergyExpenditure();

		// Sacharidy, maximum je 90 g.
		$carbs = Carbs::createFromEnergy(
			new Energy(
				new Amount(
					$wgee->getResult()->getInBaseUnit()->getAmount()->getValue() * .2,
				),
				Energy::getBaseUnit(),
			),
		);

		if ($carbs->getInUnit("g")->getAmount()->getValue() > static::CARBS_MAX) {
			$carbs = new Carbs(new Amount(static::CARBS_MAX));
		}

		$nutrients->setCarbs($carbs);

		// Tuky.
		$nutrients->setFats(
			Fats::createFromEnergy(
				new Energy(
					new Amount(
						$wgee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
					),
					Energy::getBaseUnit(),
				),
			),
		);

		return $nutrients;
	}
}
