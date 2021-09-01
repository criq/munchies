<?php

namespace Fatty\Genders;

use Fatty\Amount;
use Fatty\Birthday;
use Fatty\BodyType;
use Fatty\BreastfeedingMode;
use Fatty\BreastfeedingModes\Full;
use Fatty\BreastfeedingModes\Partial;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Exceptions\FattyException;
use Fatty\Exceptions\FattyExceptionList;
use Fatty\Percentage;

class Female extends \Fatty\Gender
{
	const ESSENTIAL_FAT_PERCENTAGE = .13;

	private $breastfeedingChildbirthDate;
	private $breastfeedingMode;
	private $isBreastfeeding;
	private $isPregnant;
	private $pregnancyChildbirthDate;

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	protected function calcBodyFatPercentageByProportions(Calculator $calculator) : Percentage
	{
		$waist = $calculator->getProportions()->getWaist()->getInCm()->getAmount()->getValue();
		$neck = $calculator->getProportions()->getNeck()->getInCm()->getAmount()->getValue();
		$height = $calculator->getProportions()->getHeight()->getInCm()->getAmount()->getValue();

		return new Percentage(((495 / (1.0324 - (0.19077 * log10($waist - $neck)) + (0.15456 * log10($height)))) - 450) * .01);
	}

	public function calcBodyFatPercentageByProportionsFormula(Calculator $calculator) : string
	{
		$waist = $calculator->getProportions()->getWaist()->getInCm()->getAmount()->getValue();
		$neck = $calculator->getProportions()->getNeck()->getInCm()->getAmount()->getValue();
		$height = $calculator->getProportions()->getHeight()->getInCm()->getAmount()->getValue();

		return '((495 / (1.0324 - (0.19077 * log10(waist[' . $waist . '] - neck[' . $neck . '])) + (0.15456 * log10(height[' . $height . '])))) - 450) * .01';
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function calcBasalMetabolicRate(Calculator $calculator) : Energy
	{
		$exceptionList = new FattyExceptionList;

		if (!$calculator->getWeight()) {
			$exceptionList->append(FattyException::createFromAbbr('missingWeight'));
		}

		if (!$calculator->getProportions()->getHeight()) {
			$exceptionList->append(FattyException::createFromAbbr('missingHeight'));
		}

		if (!$calculator->getBirthday()) {
			$exceptionList->append(FattyException::createFromAbbr('missingBirthday'));
		}

		if (count($exceptionList)) {
			throw $exceptionList;
		}

		$weight = $calculator->getWeight()->getInKg()->getAmount()->getValue();
		$height = $calculator->getProportions()->getHeight()->getInCm()->getAmount()->getValue();
		$age = $calculator->getBirthday()->getAge();

		return new Energy(new Amount((10 * $weight) + (6.25 * $height) - (5 * $age) - 161), 'kCal');
	}

	public function getBasalMetabolicRateFormula(Calculator $calculator) : string
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
			throw FattyException::createFromAbbr('missingPregnancyChildbirthDate');
		}

		if (is_string($pregnancyChildbirthDate)) {
			$pregnancyChildbirthDate = \Katu\Utils\DateTime::createFromFormat('j.n.Y', $pregnancyChildbirthDate);
		}

		if ($pregnancyChildbirthDate instanceof \DateTime) {
			$pregnancyChildbirthDate = new Birthday($pregnancyChildbirthDate);
		}

		if (!($pregnancyChildbirthDate instanceof Birthday)) {
			throw FattyException::createFromAbbr('invalidPregnancyChildbirthDate');
		}

		if ($pregnancyChildbirthDate->getBirthday()->isInPast()) {
			throw FattyException::createFromAbbr('pregnancyChildbirthDateInPast');
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
			throw FattyException::createFromAbbr('missingBreastfeedingChildbirthDate');
		}

		if (is_string($breastfeedingChildbirthDate)) {
			$breastfeedingChildbirthDate = \Katu\Utils\DateTime::createFromFormat('j.n.Y', $breastfeedingChildbirthDate);
		}

		if ($breastfeedingChildbirthDate instanceof \DateTime) {
			$breastfeedingChildbirthDate = new Birthday($breastfeedingChildbirthDate);
		}

		if (!($breastfeedingChildbirthDate instanceof Birthday)) {
			throw FattyException::createFromAbbr('invalidBreastfeedingChildbirthDate');
		}

		if ($breastfeedingChildbirthDate->getBirthday()->isInFuture()) {
			throw FattyException::createFromAbbr('breastfeedingChildbirthDateInFuture');
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
			throw FattyException::createFromAbbr('invalidBreastfeedingMode');
		}

		$this->breastfeedingMode = $breastfeedingMode;

		return $this;
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function calcReferenceDailyIntakeBonus()
	{
		$exceptionList = new FattyExceptionList;

		try {
			$referenceDailyIntakeBonusPregnancy = $this->getReferenceDailyIntakeBonusPregnancy();
		} catch (\Throwable $e) {
			$exceptionList->append($e);
		}

		try {
			$referenceDailyIntakeBonusBreastfeeding = $this->getReferenceDailyIntakeBonusBreastfeeding();
		} catch (\Throwable $e) {
			$exceptionList->append($e);
		}

		if (count($exceptionList)) {
			throw $exceptionList;
		}

		return new Energy(new Amount($referenceDailyIntakeBonusPregnancy->getAmount()->getValue() + $referenceDailyIntakeBonusBreastfeeding->getAmount()->getValue()), 'kCal');
	}

	public function getReferenceDailyIntakeBonusPregnancy()
	{
		if (!$this->isPregnant()) {
			return new Energy(new Amount(0));
		}

		if (!($this->getPregnancyChildbirthDate() instanceof \Fatty\Birthday)) {
			throw FattyException::createFromAbbr('missingPregnancyChildbirthDate');
		}

		$diff = $this->getPregnancyChildbirthDate()->diff(new \Katu\Utils\DateTime);
		if ($diff->days <= 90) {
			$change = 85;
		} elseif ($diff->days <= 180) {
			$change = 285;
		} else {
			$change = 475;
		}

		return new Energy(new Amount($change), 'kCal');
	}

	public function getReferenceDailyIntakeBonusBreastfeeding()
	{
		$exceptionList = new FattyExceptionList;

		if (!$this->isBreastfeeding()) {
			return new Energy(new Amount(0));
		}

		if (!($this->getBreastfeedingChildbirthDate() instanceof \Fatty\Birthday)) {
			$exceptionList->append(FattyException::createFromAbbr('missingBreastfeedingChildbirthDate'));
		}

		if (!($this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingMode)) {
			$exceptionList->append(FattyException::createFromAbbr('missingBreastfeedingMode'));
		}

		if (count($exceptionList)) {
			throw $exceptionList;
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

		return new Energy(new Amount($change), 'kCal');
	}

	/*****************************************************************************
	 * Typ postavy.
	 */

	public function calcBodyType(Calculator $calculator) : BodyType
	{
		$waistHipRatio = $calculator->calcWaistHipRatio();

		if ($waistHipRatio->getValue() < .75) {
			return new \Fatty\BodyTypes\PearOrHourglass;
		} elseif ($waistHipRatio->getValue() >= .75 && $waistHipRatio->getValue() < .8) {
			return new \Fatty\BodyTypes\Balanced;
		} elseif ($waistHipRatio->getValue() >= .8 && $waistHipRatio->getValue() < .85) {
			return new \Fatty\BodyTypes\Apple;
		} else {
			return new \Fatty\BodyTypes\AppleWithHigherRisk;
		}
	}
}
