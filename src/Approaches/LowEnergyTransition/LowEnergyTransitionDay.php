<?php

namespace Fatty\Approaches\LowEnergyTransition;

use Fatty\Energy;
use Fatty\Weight;

class LowEnergyTransitionDay
{
	protected $dateTime;
	protected $daysToIncrease;
	protected $weight;
	protected $weightGoalEnergyExpenditure;

	public function __construct(\DateTime $dateTime)
	{
		$this->dateTime = $dateTime;
	}

	public function getDateTime(): \DateTime
	{
		return $this->dateTime;
	}

	public function setWeight(Weight $value): LowEnergyTransitionDay
	{
		$this->weight = $value;

		return $this;
	}

	public function getWeight(): ?Weight
	{
		return $this->weight;
	}

	public function setWeightGoalEnergyExpenditure(Energy $value): LowEnergyTransitionDay
	{
		$this->weightGoalEnergyExpenditure = $value;

		return $this;
	}

	public function setDaysToIncrease(int $value): LowEnergyTransitionDay
	{
		$this->daysToIncrease = $value;

		return $this;
	}

	public function getDaysToIncrease(): ?int
	{
		return $this->daysToIncrease;
	}
}
