<?php

namespace Fatty;

use Fatty\Exceptions\MissingGoalVectorException;
use Fatty\Exceptions\MissingWeightException;

class Goal
{
	protected $duration;
	protected $vector;
	protected $weight;

	public function setDuration(?Duration $duration) : Goal
	{
		$this->duration = $duration;

		return $this;
	}

	public function getDuration() : ?Duration
	{
		return $this->duration;
	}

	public function setVector(?Vector $value) : Goal
	{
		$this->vector = $value;

		return $this;
	}

	public function getVector() : ?Vector
	{
		return $this->vector;
	}

	public function setWeight(?Weight $weight) : Goal
	{
		$this->weight = $weight;

		return $this;
	}

	public function getWeight() : ?Weight
	{
		return $this->weight;
	}

	public function getChange()
	{
		return new Weight(new Amount($this->getVector()->getChangePerWeek()->getInKg()->getAmount()->getValue() * $this->getDuration()->getInWeeks()->getAmount()->getValue()));
	}

	public function getFinal(Calculator $calculator)
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		return new Weight($weight->getInKg()->getAmount() + $this->getChange()->getInKg()->getAmount(), 'kg');
	}

	public function getDifference(Calculator $calculator)
	{
		if ($this->getVector() instanceof Vectors\Loose) {
			return $this->getFinal($calculator)->getInKg()->getAmount() - $this->getWeight()->getInKg()->getAmount();
		} elseif ($this->getVector() instanceof Vectors\Gain) {
			return $this->getWeight()->getInKg()->getAmount() - $this->getFinal($calculator)->getInKg()->getAmount();
		}
	}

	public function calcGoalTdee(Calculator $calculator)
	{
		$vector = $this->getVector();
		if (!$vector) {
			throw new MissingGoalVectorException;
		}

		return $vector->calcGoalTdee($calculator);
	}
}
