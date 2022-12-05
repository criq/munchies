<?php

namespace Fatty;

use Fatty\Metrics\QuantityMetric;
use Google\Cloud\Security\PrivateCA\V1beta1\ReusableConfig;

class Child
{
	protected $birthday;
	protected $breastfeedingMode;

	public function __construct(Birthday $birthday)
	{
		$this->setBirthday($birthday);
	}

	public function setBirthday(Birthday $birthday): Child
	{
		$this->birthday = $birthday;

		return $this;
	}

	public function getBirthday(): Birthday
	{
		return $this->birthday;
	}

	public function setBreastfeedingMode(?BreastfeedingMode $breastfeedingMode): Child
	{
		$this->breastfeedingMode = $breastfeedingMode;

		return $this;
	}

	public function getBreastfeedingMode(): ?BreastfeedingMode
	{
		return $this->breastfeedingMode;
	}

	public function calcReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetric
	{
		$energy = new Energy(new Amount, "kJ");
		$ageInMonths = $this->getBirthday()->getAgeInMonths($calculator->getReferenceTime());
		$isGainingWeight = $calculator->calcWeightGoalQuotient($calculator)->getResult()->getValue() >= 1;

		if ($ageInMonths < 1) {
			$energy->modify(new Energy(new Amount($isGainingWeight ? 2380 : 1730), "kJ"));
		} elseif ($ageInMonths < 2) {
			$energy->modify(new Energy(new Amount($isGainingWeight ? 2730 : 2080), "kJ"));
		} elseif ($ageInMonths < 3) {
			$energy->modify(new Energy(new Amount($isGainingWeight ? 2870 : 2220), "kJ"));
		} elseif ($ageInMonths < 6) {
			if ($this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingModes\Full) {
				$energy->modify(new Energy(new Amount($isGainingWeight ? 2870 : 2300), "kJ"));
			} elseif ($this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingModes\Partial) {
				$energy->modify(new Energy(new Amount($isGainingWeight ? 1430 : 1150), "kJ"));
			}
		} elseif ($ageInMonths < 12) {
			if ($this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingModes\Full) {
				$energy->modify(new Energy(new Amount($isGainingWeight ? 2275 : 1820), "kJ"));
			} elseif ($this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingModes\Partial) {
				$energy->modify(new Energy(new Amount($isGainingWeight ? 1140 : 910), "kJ"));
			}
		} elseif ($ageInMonths < 24) {
			if ($this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingModes\Full) {
				$energy->modify(new Energy(new Amount($isGainingWeight ? 2100 : 1680), "kJ"));
			} elseif ($this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingModes\Partial) {
				$energy->modify(new Energy(new Amount($isGainingWeight ? 1050 : 840), "kJ"));
			}
		} elseif ($this->getBreastfeedingMode()) {
			$energy->modify(new Energy(new Amount($isGainingWeight ? 500 : 400), "kJ"));
		}

		return new QuantityMetric(
			"referenceDailyIntakeBonus",
			$energy,
		);
	}
}
