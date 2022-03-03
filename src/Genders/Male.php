<?php

namespace Fatty\Genders;

use Fatty\Amount;
use Fatty\BodyTypes\Apple;
use Fatty\BodyTypes\AppleWithHigherRisk;
use Fatty\BodyTypes\Balanced;
use Fatty\BodyTypes\PearOrHourglass;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Exceptions\FattyExceptionCollection;
use Fatty\Exceptions\MissingBirthdayException;
use Fatty\Exceptions\MissingHeightException;
use Fatty\Exceptions\MissingWeightException;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\AmountWithUnitMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Percentage;

class Male extends \Fatty\Gender
{
	const ESSENTIAL_FAT_PERCENTAGE = .5;

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	protected function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetric
	{
		$waistValue = $calculator->getProportions()->getWaist()->getInUnit("cm")->getAmount()->getValue();
		$neckValue = $calculator->getProportions()->getNeck()->getInUnit("cm")->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();

		$result = new Percentage(((495 / (1.0324 - (0.19077 * log10($waistValue - $neckValue)) + (0.15456 * log10($heightValue)))) - 450) * .01);
		$formula = "((495 / (1.0324 - (0.19077 * log10(waist[{$waistValue}] - neck[{$neckValue}])) + (0.15456 * log10(height[{$heightValue}])))) - 450) * .01 = {$result->getValue()}";

		return new AmountMetric("bodyFatPercentage", $result, $formula);
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function calcBasalMetabolicRate(Calculator $calculator): AmountWithUnitMetric
	{
		$exceptionCollection = new FattyExceptionCollection;

		if (!$calculator->getWeight()) {
			$exceptionCollection->add(new MissingWeightException);
		}

		if (!$calculator->getProportions()->getHeight()) {
			$exceptionCollection->add(new MissingHeightException);
		}

		if (!$calculator->getBirthday()) {
			$exceptionCollection->add(new MissingBirthdayException);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		$weightValue = $calculator->getWeight()->getInUnit("kg")->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();
		$age = $calculator->getBirthday()->getAge();

		$resultValue = (10 * $weightValue) + (6.25 * $heightValue) - (5 * $age) + 5;
		$result = (new Energy(new Amount($resultValue), "kcal"))
			->getInUnit($calculator->getUnits())
			;

		$formula = "
			(10 * weight[{$weightValue}]) + (6.25 * height[{$heightValue}]) - (5 * age[{$age}]) + 5
			= " . (10 * $weightValue) . " + " . (6.25 * $heightValue) . " - " . (5 * $age) . " + 5
			= {$resultValue} kcal
		";

		return new AmountWithUnitMetric("basalMetabolicRate", $result, $formula);
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
}
