<?php

namespace Fatty\Approaches\DiaMama;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\GoalNutrientsProteinsMetric;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Nutrients;
use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;
use Fatty\Nutrients\Proteins;

class Standard extends \Fatty\Approaches\Standard
{
	const CARBS_DEFAULT = 115;
	const CODE = "DIAMAMA_STANDARD";
	const LABEL_DECLINATED = "standardní dietu";

	public function calcGoalNutrientsProteins(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new GoalNutrientsProteinsMetric);

		$estimatedFunctionalMassResult = $calculator->calcEstimatedFunctionalMass();
		$result->addErrors($estimatedFunctionalMassResult->getErrors());

		$sportProteinCoefficientResult = $calculator->calcSportProteinCoefficient();
		$result->addErrors($sportProteinCoefficientResult->getErrors());

		$goalNutrientProteinBonusResult = $calculator->getGender()->calcGoalNutrientProteinBonus($calculator);
		$result->addErrors($goalNutrientProteinBonusResult->getErrors());

		if (!$result->hasErrors()) {
			$estimatedFunctionalMassValue = $estimatedFunctionalMassResult->getResult()->getNumericalValue();
			$sportProteinCoefficientValue = $sportProteinCoefficientResult->getResult()->getNumericalValue();
			$goalNutrientProteinBonusValue = $goalNutrientProteinBonusResult->getResult()->getNumericalValue();

			$value = ($estimatedFunctionalMassValue * $sportProteinCoefficientValue) + $goalNutrientProteinBonusValue;
			$proteins = new Proteins(new Amount($value), "g");

			$formula = "
				(estimatedFunctionalMass[$estimatedFunctionalMassValue] * sportProteinCoefficient[$sportProteinCoefficientValue]) + $goalNutrientProteinBonusValue
				" . ($estimatedFunctionalMassValue * $sportProteinCoefficientValue) . " + $goalNutrientProteinBonusValue
				= $value
			";

			$result->setResult($proteins)->setFormula($formula);
		}

		return $result;
	}

	public function getGoalNutrients(Calculator $calculator): Nutrients
	{
		$wgee = $calculator->calcWeightGoalEnergyExpenditure();

		$nutrients = new Nutrients;
		$nutrients->setProteins($this->calcGoalNutrientsProteins($calculator)->getResult());
		$nutrients->setCarbs(new Carbs(new Amount(static::CARBS_DEFAULT), "g"));

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
