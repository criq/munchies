<?php

namespace Fatty\Genders;

use Fatty\Amount;
use Fatty\ArrayValue;
use Fatty\BodyTypes\Apple;
use Fatty\BodyTypes\AppleWithHigherRisk;
use Fatty\BodyTypes\Balanced;
use Fatty\BodyTypes\PearOrHourglass;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\ArrayMetricResult;
use Fatty\Metrics\BodyFatPercentageMetric;
use Fatty\Metrics\BodyTypeMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\SportProteinMatrixMetric;
use Fatty\Metrics\StringMetricResult;
use Fatty\Percentage;
use Fatty\StringValue;

class Male extends \Fatty\Gender
{
	const ESSENTIAL_FAT_PERCENTAGE = 0.5;
	const FIT_BODY_FAT_PERCENTAGE = 0.19;

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetricResult
	{
		$result = new AmountMetricResult(new BodyFatPercentageMetric);

		$waistValue = $calculator->getProportions()->getWaist()->getInUnit("cm")->getAmount()->getValue();
		$neckValue = $calculator->getProportions()->getNeck()->getInUnit("cm")->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();

		$resultValue = ((495 / (1.0324 - (0.19077 * log10($waistValue - $neckValue)) + (0.15456 * log10($heightValue)))) - 450) * .01;
		$percentage = new Percentage($resultValue);
		$formula = "
			((495 / (1.0324 - (0.19077 * log10(waist[{$waistValue}] - neck[{$neckValue}])) + (0.15456 * log10(height[{$heightValue}])))) - 450) * .01
			= {$resultValue}
			";

		return $result->setResult($percentage)->setFormula($formula);
	}

	/****************************************************************************
	 * Basal metabolic rate.
	 */
	public function calcBasalMetabolicRateMifflinStJeorAdjustment(): QuantityMetricResult
	{
		return new QuantityMetric(
			"basalMetabolicRateMifflinStJeorAdjustment",
			new Energy(new Amount(5), "kcal"),
		);
	}

	/*****************************************************************************
	 * Typ postavy.
	 */
	public function calcBodyType(Calculator $calculator): StringMetricResult
	{
		$result = new StringMetricResult(new BodyTypeMetric);

		$waistHipRatioResult = $calculator->calcWaistHipRatio();
		$result->addErrors($waistHipRatioResult->getErrors());

		if (!$result->hasErrors()) {
			$waistHipRatioValue = $waistHipRatioResult->getResult()->getNumericalValue();

			if ($waistHipRatioValue < .85) {
				$bodyType = new PearOrHourglass;
			} elseif ($waistHipRatioValue >= .8 && $waistHipRatioValue < .9) {
				$bodyType = new Balanced;
			} elseif ($waistHipRatioValue >= .9 && $waistHipRatioValue < .95) {
				$bodyType = new Apple;
			} else {
				$bodyType = new AppleWithHigherRisk;
			}

			$result->setResult(new StringValue($bodyType->getCode()));
		}

		return $result;
	}

	/****************************************************************************
	 * Sport protein matrix.
	 */
	public function calcSportProteinMatrix(): ArrayMetricResult
	{
		return (new ArrayMetricResult(new SportProteinMatrixMetric))
			->setResult(new ArrayValue([
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
			]))
			;
	}
}
