<?php

namespace Fatty\Genders;

use Fatty\Amount;
use Fatty\BodyTypes\Apple;
use Fatty\BodyTypes\AppleWithHigherRisk;
use Fatty\BodyTypes\Balanced;
use Fatty\BodyTypes\PearOrHourglass;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Percentage;

class Male extends \Fatty\Gender
{
	const DEFAULT_SPORT_PROTEIN_COEFFICIENT = 1.5;
	const ESSENTIAL_FAT_PERCENTAGE = 0.5;
	const FIT_BODY_FAT_PERCENTAGE = 0.19;

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetric
	{
		$waistValue = $calculator->getProportions()->getWaist()->getInUnit("cm")->getAmount()->getValue();
		$neckValue = $calculator->getProportions()->getNeck()->getInUnit("cm")->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();

		$resultValue = ((495 / (1.0324 - (0.19077 * log10($waistValue - $neckValue)) + (0.15456 * log10($heightValue)))) - 450) * .01;
		$result = new Percentage($resultValue);
		$formula = "
			((495 / (1.0324 - (0.19077 * log10(waist[{$waistValue}] - neck[{$neckValue}])) + (0.15456 * log10(height[{$heightValue}])))) - 450) * .01
			= {$resultValue}
			";

		return new AmountMetric("bodyFatPercentage", $result, $formula);
	}

	/****************************************************************************
	 * Basal metabolic rate.
	 */
	public function calcBasalMetabolicRateMifflinStJeorAdjustment(): QuantityMetric
	{
		return new QuantityMetric(
			"basalMetabolicRateMifflinStJeorAdjustment",
			new Energy(new Amount(5), "kcal"),
		);
	}

	/*****************************************************************************
	 * Typ postavy.
	 */
	public function calcBodyType(Calculator $calculator): StringMetric
	{
		$waistHipRatioValue = $calculator->calcWaistHipRatio()->getResult()->getValue();

		if ($waistHipRatioValue < .85) {
			$result = new PearOrHourglass;
		} elseif ($waistHipRatioValue >= .8 && $waistHipRatioValue < .9) {
			$result = new Balanced;
		} elseif ($waistHipRatioValue >= .9 && $waistHipRatioValue < .95) {
			$result = new Apple;
		} else {
			$result = new AppleWithHigherRisk;
		}

		return new StringMetric("bodyType", $result->getCode(), $result->getLabel());
	}

	/****************************************************************************
	 * Sport protein matrix.
	 */
	public function getSportProteinMatrix(): array
	{
		return [
			"FIT" => [
				"AEROBIC"         => 1.8,
				"ANAEROBIC_LONG"  => 2.2,
				"ANAEROBIC_SHORT" => 1.8,
				"ANAEROBIC"       => 2.2,
				"LOW_FREQUENCY"   => 1.5,
				"NO_ACTIVITY"     => 1.5,
			],
			"UNFIT" => [
				"AEROBIC"         => 1.7,
				"ANAEROBIC_LONG"  => 2,
				"ANAEROBIC_SHORT" => 1.7,
				"ANAEROBIC"       => 2,
				"LOW_FREQUENCY"   => 1.5,
				"NO_ACTIVITY"     => 1.5,
			],
		];
	}
}
