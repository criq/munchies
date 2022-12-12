<?php

namespace Fatty\Approaches\DiaMama;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Metrics\QuantityMetric;
use Fatty\Nutrients\Proteins;

class Standard extends \Fatty\Approaches\Standard
{
	const CARBS_DEFAULT = 150;
	const CODE = "DIAMAMA_STANDARD";
	const LABEL_DECLINATED = "standardní dietu";

	public function calcGoalNutrientProteins(Calculator $calculator): QuantityMetric
	{
		$estimatedFunctionalMass = $calculator->calcEstimatedFunctionalMass()->getResult();
		$estimatedFunctionalMassValue = $estimatedFunctionalMass->getAmount()->getValue();

		$sportProteinCoefficient = $calculator->calcSportProteinCoefficient()->getResult();
		$sportProteinCoefficientValue = $sportProteinCoefficient->getValue();

		// TODO - bonusy za kojení
		// var_dump($calculator->getGender()->getChildren());die;

		$resultValue = $estimatedFunctionalMassValue * $sportProteinCoefficientValue;

		$formula = "
			(estimatedFunctionalMass[$estimatedFunctionalMass] * sportProteinCoefficient[$sportProteinCoefficient])
			= $resultValue
		";

		$proteins = new Proteins(new Amount($resultValue), "g");

		return new QuantityMetric(
			"goalNutrientsProteins",
			$proteins,
			$formula,
		);
	}
}
