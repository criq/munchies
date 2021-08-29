<?php

namespace Fatty\Genders;

use \Fatty\Birthday;
use \Fatty\BreastfeedingMode;
use \Fatty\BreastfeedingModes\Full;
use \Fatty\BreastfeedingModes\Partial;
use \Fatty\Energy;
use \Fatty\Exceptions\CaloricCalculatorException;
use \Fatty\Length;
use \Fatty\Percentage;
use \Fatty\Proportions;
use \Fatty\Weight;

class Female extends \Fatty\Gender
{
	private $breastfeedingChildbirthDate;
	private $breastfeedingMode;
	private $isBreastfeeding;
	private $isPregnant;
	private $pregnancyChildbirthDate;

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

		return new Energy((10 * $calculator->getWeight()->getInKg()->getAmount()) + (6.25 * $calculator->getProportions()->getHeight()->getInCm()->getAmount()) - (5 * $calculator->getBirthday()->getAge()) - 161, 'kCal');
	}

	public function getBasalMetabolicRateFormula(&$calculator)
	{
		return '(10 * weight[' . $calculator->getWeight()->getInKg()->getAmount() . ']) + (6.25 * height[' . $calculator->getProportions()->getHeight()->getInCm()->getAmount() . ']) - (5 * age[' . $calculator->getBirthday()->getAge() . ']) - 161';
	}

	/*****************************************************************************
	 * Těhotenství.
	 */
	public function isPregnant()
	{
		return $this->isPregnant;
	}

	public function setIsPregnant($isPregnant)
	{
		$this->isPregnant = (bool)$isPregnant;
	}

	public function setPregnancyChildbirthDate($pregnancyChildbirthDate)
	{
		if (!$pregnancyChildbirthDate) {
			throw (new CaloricCalculatorException("Missing pregnancy childbirth date."))
				->setAbbr('missingPregnancyChildbirthDate')
				;
		}

		if (is_string($pregnancyChildbirthDate)) {
			$pregnancyChildbirthDate = \Katu\Utils\DateTime::createFromFormat('j.n.Y', $pregnancyChildbirthDate);
		}

		if ($pregnancyChildbirthDate instanceof \DateTime) {
			$pregnancyChildbirthDate = new Birthday($pregnancyChildbirthDate);
		}

		if (!($pregnancyChildbirthDate instanceof Birthday)) {
			throw (new CaloricCalculatorException("Invalid pregnancy childbirth date."))
				->setAbbr('invalidPregnancyChildbirthDate')
				;
		}

		if ($pregnancyChildbirthDate->getBirthday()->isInPast()) {
			throw (new CaloricCalculatorException("Pregnancy childbirth date is in past."))
				->setAbbr('pregnancyChildbirthDateInPast')
				;
		}

		$this->pregnancyChildbirthDate = $pregnancyChildbirthDate;

		return $this;
	}

	/*****************************************************************************
	 * Kojení.
	 */
	public function setIsBreastfeeding($isBreastfeeding)
	{
		$this->isBreastfeeding = (bool)$isBreastfeeding;
	}

	public function isBreastfeeding()
	{
		return $this->isBreastfeeding;
	}

	public function setBreastfeedingChildbirthDate($breastfeedingChildbirthDate)
	{
		if (!$breastfeedingChildbirthDate) {
			throw (new CaloricCalculatorException("Missing breastfeeding childbirth date."))
				->setAbbr('missingBreastfeedingChildbirthDate')
				;
		}

		if (is_string($breastfeedingChildbirthDate)) {
			$breastfeedingChildbirthDate = \Katu\Utils\DateTime::createFromFormat('j.n.Y', $breastfeedingChildbirthDate);
		}

		if ($breastfeedingChildbirthDate instanceof \DateTime) {
			$breastfeedingChildbirthDate = new Birthday($breastfeedingChildbirthDate);
		}

		if (!($breastfeedingChildbirthDate instanceof Birthday)) {
			throw (new CaloricCalculatorException("Invalid breastfeeding childbirth date."))
				->setAbbr('invalidBreastfeedingChildbirthDate')
				;
		}

		if ($breastfeedingChildbirthDate->getBirthday()->isInFuture()) {
			throw (new CaloricCalculatorException("Breastfeeding childbirth date is in future."))
				->setAbbr('breastfeedingChildbirthDateInFuture')
				;
		}

		$this->breastfeedingChildbirthDate = $breastfeedingChildbirthDate;

		return $this;
	}

	public function setBreastfeedingMode($breastfeedingMode)
	{
		if (is_string($breastfeedingMode)) {
			$className = "Fatty\\BreastfeedingModes\\" . ucfirst($breastfeedingMode);
			if (class_exists($className)) {
				$breastfeedingMode = new $className;
			}
		}

		if (!($breastfeedingMode instanceof BreastfeedingMode)) {
			throw (new CaloricCalculatorException("Invalid breastfeeding mode."))
				->setAbbr('invalidBreastfeedingMode')
				;
		}

		$this->breastfeedingMode = $breastfeedingMode;

		return $this;
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function getReferenceDailyIntakeBonus()
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		try {
			$referenceDailyIntakeBonusPregnancy = $this->getReferenceDailyIntakeBonusPregnancy();
		} catch (\Exception $e) {
			$ec->add($e);
		}

		try {
			$referenceDailyIntakeBonusBreastfeeding = $this->getReferenceDailyIntakeBonusBreastfeeding();
		} catch (\Exception $e) {
			$ec->add($e);
		}

		if ($ec->has()) {
			throw $ec;
		}

		return new Energy($referenceDailyIntakeBonusPregnancy->getAmount() + $referenceDailyIntakeBonusBreastfeeding->getAmount(), 'kCal');
	}

	public function getReferenceDailyIntakeBonusPregnancy()
	{
		if (!$this->isPregnant()) {
			return new Energy(0);
		}

		if (!($this->getPregnancyChildbirthDate() instanceof \Fatty\Birthday)) {
			throw (new CaloricCalculatorException("Missing pregnancy childbirth date."))
				->setAbbr('missingPregnancyChildbirthDate')
				;
		}

		$diff = $this->getPregnancyChildbirthDate()->diff(new \Katu\Utils\DateTime);
		if ($diff->days <= 90) {
			$change = 85;
		} elseif ($diff->days <= 180) {
			$change = 285;
		} else {
			$change = 475;
		}

		return new Energy($change, 'kCal');
	}

	public function getReferenceDailyIntakeBonusBreastfeeding()
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		if (!$this->isBreastfeeding()) {
			return new Energy(0);
		}

		if (!($this->getBreastfeedingChildbirthDate() instanceof \Fatty\Birthday)) {
			$ec->add((new CaloricCalculatorException("Missing breastfeeding childbirth date."))
				->setAbbr('missingBreastfeedingChildbirthDate'));
		}

		if (!($this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingMode)) {
			$ec->add((new CaloricCalculatorException("Missing breastfeeding mode."))
				->setAbbr('missingBreastfeedingMode'));
		}

		if ($ec->has()) {
			throw $ec;
		}

		$diff = $this->getBreastfeedingChildbirthDate()->diff(new \Katu\Utils\DateTime);
		if ($diff->days <= 365 / 12 * 3) {
			$change = 650;
		} elseif ($this->getBreastfeedingMode() instanceof Full && $diff->days <= 365 / 12 * 6) {
			$change = 570;
		} elseif ($this->getBreastfeedingMode() instanceof Full && $diff->days <= 365) {
			$change = 455;
		} elseif ($this->getBreastfeedingMode() instanceof Full && $diff->days <= 365 * 2) {
			$change = 420;
		} elseif ($this->getBreastfeedingMode() instanceof Partial && $diff->days <= 365 / 12 * 6) {
			$change = 280;
		} elseif ($this->getBreastfeedingMode() instanceof Partial && $diff->days <= 365) {
			$change = 230;
		} elseif ($this->getBreastfeedingMode() instanceof Partial && $diff->days <= 365 * 2) {
			$change = 210;
		} else {
			$change = 100;
		}

		return new Energy($change, 'kCal');
	}

	/*****************************************************************************
	 * Typ postavy.
	 */

	public function getBodyType(&$calculator)
	{
		$waistHipRatioAmount = $calculator->getWaistHipRatio()->getAmount();

		if ($waistHipRatioAmount < .75) {
			// @TODO - hruška nebo přesýpací hodiny - budu muset zahrnout prsa.
			return new \Fatty\BodyTypes\PearOrHourglass;
		} elseif ($waistHipRatioAmount >= .75 && $waistHipRatioAmount < .8) {
			return new \Fatty\BodyTypes\Balanced;
		} elseif ($waistHipRatioAmount >= .8 && $waistHipRatioAmount < .85) {
			return new \Fatty\BodyTypes\Apple;
		} else {
			return new \Fatty\BodyTypes\AppleWithHigherRisk;
		}
	}
}
