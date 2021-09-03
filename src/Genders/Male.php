<?php

namespace Fatty\Genders;

use Fatty\Amount;
use Fatty\BodyType;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Exceptions\FattyExceptionCollection;
use Fatty\Exceptions\MissingBirthdayException;
use Fatty\Exceptions\MissingHeightException;
use Fatty\Exceptions\MissingWeightException;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\AmountWithUnitMetric;
use Fatty\Percentage;

class Male extends \Fatty\Gender
{
	const ESSENTIAL_FAT_PERCENTAGE = .5;

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	protected function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetric
	{
		$waist = $calculator->getProportions()->getWaist()->getInUnit('cm')->getAmount()->getValue();
		$neck = $calculator->getProportions()->getNeck()->getInUnit('cm')->getAmount()->getValue();
		$height = $calculator->getProportions()->getHeight()->getInUnit('cm')->getAmount()->getValue();

		$result = new Percentage(((495 / (1.0324 - (0.19077 * log10($waist - $neck)) + (0.15456 * log10($height)))) - 450) * .01);
		$formula = '((495 / (1.0324 - (0.19077 * log10(waist[' . $waist . '] - neck[' . $neck . '])) + (0.15456 * log10(height[' . $height . '])))) - 450) * .01';

		return new AmountMetric('bodyFatPercentage', $result, $formula);
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

		$weightValue = $calculator->getWeight()->getInUnit('kg')->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit('cm')->getAmount()->getValue();
		$age = $calculator->getBirthday()->getAge();

		$result = new Energy(
			new Amount(
				(10 * $weightValue) + (6.25 * $heightValue) - (5 * $age) + 5
			),
			'kCal',
		);

		$formula = '(10 * weight[' . $weightValue . ']) + (6.25 * height[' . $heightValue . ']) - (5 * age[' . $age . ']) + 5';

		return new AmountWithUnitMetric('basalMetabolicRate', $result, $formula);
	}

	/*****************************************************************************
	 * Typ postavy.
	 */
	public function calcBodyType(Calculator $calculator): BodyType
	{
		$waistHipRatioValue = $calculator->calcWaistHipRatio()->getResult()->getValue();

		if ($waistHipRatioValue < .85) {
			return new \Fatty\BodyTypes\PearOrHourglass;
		} elseif ($waistHipRatioValue >= .8 && $waistHipRatioValue < .9) {
			return new \Fatty\BodyTypes\Balanced;
		} elseif ($waistHipRatioValue >= .9 && $waistHipRatioValue < .95) {
			return new \Fatty\BodyTypes\Apple;
		} else {
			return new \Fatty\BodyTypes\AppleWithHigherRisk;
		}
	}
}
