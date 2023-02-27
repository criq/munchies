<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Nutrients;
use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;

class Mediterranean extends Standard
{
	const CODE = "MEDITERRANEAN";
	const LABEL_DECLINATED = "středomořskou dietu";

	public function getGoalNutrients(Calculator $calculator): Nutrients
	{
		$nutrients = new Nutrients;
		$nutrients->setProteins($this->calcGoalNutrientsProteins($calculator)->getResult());

		$wgee = $calculator->calcWeightGoalEnergyExpenditure();

		$nutrients->setFats(
			Fats::createFromEnergy(
				new Energy(
					new Amount(
						$wgee->getResult()->getInBaseUnit()->getAmount()->getValue() * .4
					),
					Energy::getBaseUnit(),
				),
			),
		);

		$nutrients->setCarbs(
			Carbs::createFromEnergy(
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
