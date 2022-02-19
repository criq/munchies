<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Genders\Female;
use Fatty\Nutrients;
use Fatty\Nutrients\Fats;

class Keto extends \Fatty\Approach
{
	const CARBS_DEFAULT = 40;
	const CARBS_MAX = 50;
	const CARBS_MIN = 0;
	const CODE = "KETO";
	const LABEL_DECLINATED = "keto dietu";

	public function getGoalNutrients(Calculator $calculator): Nutrients
	{
		$nutrients = new Nutrients;
		$nutrients->setProteins($this->calcGoalNutrientProteins($calculator)->getResult());

		$wgee = $calculator->calcWeightGoalEnergyExpenditure();

		// TODO
		if (false && $calculator->getGender() instanceof Female && $calculator->getGender()->isPregnant()) {
		// TODO
		} elseif (false && $calculator->getGender() instanceof Female && $calculator->getGender()->isBreastfeeding()) {
		} else {
			$dietCarbs = $calculator->getDiet()->getCarbs();
			$nutrients->setCarbs($dietCarbs);
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
