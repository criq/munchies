<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Genders\Female;
use Fatty\Nutrients;
use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;
use Fatty\SportDuration;

class Standard extends \Fatty\Approach
{
	const CARBS_DEFAULT = 120;
	const CODE = "STANDARD";
	const LABEL_DECLINATED = "standardní dietu";

	public function getGoalNutrients(Calculator $calculator): Nutrients
	{
		$nutrients = new Nutrients;
		$nutrients->setProteins($this->calcGoalNutrientProteins($calculator)->getResult());

		$wgee = $calculator->calcWeightGoalEnergyExpenditure();

		if ($calculator->getSportDurations()->getAnaerobic() instanceof SportDuration && $calculator->getSportDurations()->getAnaerobic()->getAmount()->getValue() >= 100) {
			$nutrients->setCarbs(
				Carbs::createFromEnergy(
					new Energy(new Amount($wgee->getResult()->getInUnit("kJ")->getAmount()->getValue() * .58), "kJ"),
				)
			);

			$nutrients->setFats(Fats::createFromEnergy(
				new Energy(
					new Amount(
						$wgee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue(),
					),
					Energy::getBaseUnit(),
				),
			));
		} elseif ($calculator->getGender() instanceof Female && ($calculator->getGender()->getIsPregnant() || $calculator->getGender()->isBreastfeeding())) {
			$nutrients->setFats(
				Fats::createFromEnergy(
					new Energy(
						new Amount(
							$wgee->getResult()->getInBaseUnit()->getAmount()->getValue() * .35
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
		} else {
			$nutrients->setCarbs(
				Carbs::createFromEnergy(
					new Energy(
						new Amount(
							$wgee->getResult()->getInBaseUnit()->getAmount()->getValue() * .55
						),
						Energy::getBaseUnit(),
					),
				),
			);

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
