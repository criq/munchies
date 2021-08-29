<?php

namespace Fatty\Genders;

use \Fatty\Birthday;
use \Fatty\Energy;
use \Fatty\Exceptions\CaloricCalculatorException;
use \Fatty\Length;
use \Fatty\Percentage;
use \Fatty\Proportions;
use \Fatty\Weight;

class Male extends \Fatty\Gender
{
	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	protected function getBodyFatPercentageByProportions(&$calculator)
	{
		return new Percentage(((495 / (1.0324 - (0.19077 * log10($calculator->getProportions()->getWaist()->getInCm()->getAmount() - $calculator->getProportions()->getNeck()->getInCm()->getAmount())) + (0.15456 * log10($calculator->getProportions()->getHeight()->getInCm()->getAmount())))) - 450) * .01);
	}

	public function getBodyFatPercentageByProportionsFormula(&$calculator)
	{
		return '((495 / (1.0324 - (0.19077 * log10(waist[' . $calculator->getProportions()->getWaist()->getInCm()->getAmount() . '] - neck[' . $calculator->getProportions()->getNeck()->getInCm()->getAmount() . '])) + (0.15456 * log10(height[' . $calculator->getProportions()->getHeight()->getInCm()->getAmount() . '])))) - 450) * .01';
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function getBasalMetabolicRate(&$calculator)
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		if (!($calculator->getWeight() instanceof Weight)) {
			$ec->add((new CaloricCalculatorException("Missing weight."))
				->setAbbr('missingWeight'));
		}

		if (!($calculator->getProportions() instanceof Proportions)) {
			$ec->add((new CaloricCalculatorException("Missing proportions."))
				->setAbbr('missingProportions'));
		}

		if (!($calculator->getProportions()->getHeight() instanceof Length)) {
			$ec->add((new CaloricCalculatorException("Missing height."))
				->setAbbr('missingHeight'));
		}

		if (!($calculator->getBirthday() instanceof Birthday)) {
			$ec->add((new CaloricCalculatorException("Missing birthday."))
				->setAbbr('missingBirthday'));
		}

		if ($ec->has()) {
			throw $ec;
		}

		return new Energy((10 * $calculator->getWeight()->getInKg()->getAmount()) + (6.25 * $calculator->getProportions()->getHeight()->getInCm()->getAmount()) - (5 * $calculator->getBirthday()->getAge()) + 5, 'kCal');
	}

	public function getBasalMetabolicRateFormula(&$calculator)
	{
		return '(10 * weight[' . $calculator->getWeight()->getInKg()->getAmount() . ']) + (6.25 * height[' . $calculator->getProportions()->getHeight()->getInCm()->getAmount() . ']) - (5 * age[' . $calculator->getBirthday()->getAge() . ']) + 5';
	}

	/*****************************************************************************
	 * Typ postavy.
	 */
	public function getBodyType(&$calculator)
	{
		$waistHipRatioAmount = $calculator->getWaistHipRatio()->getAmount();

		if ($waistHipRatioAmount < .85) {
			return new \Fatty\BodyTypes\PearOrHourglass;
		} elseif ($waistHipRatioAmount >= .8 && $waistHipRatioAmount < .9) {
			return new \Fatty\BodyTypes\Balanced;
		} elseif ($waistHipRatioAmount >= .9 && $waistHipRatioAmount < .95) {
			return new \Fatty\BodyTypes\Apple;
		} else {
			return new \Fatty\BodyTypes\AppleWithHigherRisk;
		}
	}
}