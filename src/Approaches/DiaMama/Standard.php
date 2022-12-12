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
		$estimatedFunctionalMass = $calculator->calcEstimatedFunctionalMass();

		var_dump($calculator->getSportDurations()->getMaxProteinSportDuration());
		die;

		$resultValue = $estimatedFunctionalMass->getResult()->getAmount()->getValue() * $calcSportProteinCoefficient->getResult()->getValue();

		// TODO - bonusy za kojení
		// var_dump($calculator->getGender()->getChildren());die;

		$proteins = new Proteins(new Amount($resultValue), "g");

		return new \Fatty\Metrics\QuantityMetric("goalNutrientsProteins", $proteins);
	}
}
