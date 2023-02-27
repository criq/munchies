<?php

namespace Fatty;

use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\ReferenceDailyIntakeBonusMetric;

class Child
{
	protected $birthday;
	protected $breastfeedingMode;
	protected $name;

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

	public function setName(?string $name): Child
	{
		$this->name = $name ?: null;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
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

	public function getIsBreastfed(): bool
	{
		return $this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingModes\Full || $this->getBreastfeedingMode() instanceof \Fatty\BreastfeedingModes\Partial;
	}

	public function calcReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new ReferenceDailyIntakeBonusMetric);

		$weightGoalQuotientResult = $calculator->calcWeightGoalQuotient($calculator);
		$result->addErrors($weightGoalQuotientResult->getErrors());

		if (!$result->hasErrors()) {
			$isGainingWeight = $weightGoalQuotientResult->getResult()->getNumericalValue() >= 1;

			$energy = new Energy(new Amount, "kJ");
			$ageInMonths = $this->getBirthday()->getAgeInMonths($calculator->getReferenceTime());

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

			$result->setResult($energy);
		}

		return $result;
	}
}
