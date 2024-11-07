<?php

namespace Fatty\Approaches\LowEnergyTransition;

use Fatty\Energy;
use Fatty\Weight;
use Katu\Tools\Calendar\Time;

class LowEnergyTransitionDay
{
	protected $daysToIncrease;
	protected $isTransitionFinished = false;
	protected $time;
	protected $weight;
	protected $weightGoalEnergyExpenditure;

	public function __construct(Time $time)
	{
		$this->time = $time;
	}

	public function getTime(): Time
	{
		return $this->time;
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

	public function setWeightGoalEnergyExpenditure(?Energy $value): LowEnergyTransitionDay
	{
		$this->weightGoalEnergyExpenditure = $value;

		return $this;
	}

	public function getWeightGoalEnergyExpenditure(): ?Energy
	{
		return $this->weightGoalEnergyExpenditure;
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

	public function setIsTransitionFinished(bool $value): LowEnergyTransitionDay
	{
		$this->isTransitionFinished = $value;

		return $this;
	}

	public function getIsTransitionFinished(): bool
	{
		return $this->isTransitionFinished;
	}
}
