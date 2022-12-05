<?php

namespace Fatty;

use Fatty\Metrics\QuantityMetric;

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

	public function setBreastfeedingMode(BreastfeedingMode $breastfeedingMode): Child
	{
		$this->breastfeedingMode = $breastfeedingMode;

		return $this;
	}

	public function getReferenceDailyIntakeBonus(Calculator $calculator)//: QuantityMetric
	{
		$weightGoalQuotientValue = $calculator->calcWeightGoalQuotient($calculator)->getResult()->getValue();

		var_dump($this->getBirthday()->getAge($calculator->getReferenceTime()));
	}
}
