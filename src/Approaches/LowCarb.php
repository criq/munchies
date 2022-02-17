<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Genders\Female;
use Fatty\Nutrients;
use Fatty\Nutrients\Fats;

class LowCarb extends \Fatty\Approach
{
	const CARBS_DEFAULT = 80;
	const CARBS_MAX = 120;
	const CARBS_MIN = 50;
	const CODE = "LOW_CARB";
	const LABEL_DECLINATED = "nízkosacharidovou dietu";

	public function getGoalNutrients(Calculator $calculator): Nutrients
	{
		$nutrients = new Nutrients;
		$nutrients->setProteins($this->calcGoalNutrientProteins($calculator)->getResult());

		$wgee = $calculator->calcWeightGoalEnergyExpenditure();

		// Pregnant.
		if ($calculator->getGender() instanceof Female && $calculator->getGender()->isPregnant()) {
			$nutrients->setCarbs($calculator->getDiet()->getCarbs());
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
		// Breastfeeding.
		} elseif ($calculator->getGender() instanceof Female && $calculator->getGender()->isBreastfeeding()) {
			$nutrients->setCarbs($calculator->getDiet()->getCarbs());
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
		} else {
			$nutrients->setCarbs($calculator->getDiet()->getCarbs());
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
		}

		return $nutrients;
	}
}
