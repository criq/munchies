<?php

namespace Fatty;

use Fatty\Exceptions\FattyException;

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

	public function getFinal(&$calculator)
	{
		$weight = $this->getWeight();
		if (!($weight instanceof Weight)) {
			throw (new FattyException("Missing weight."))
				->setAbbr('missingWeight')
				;
		}

		return new Weight($weight->getInKg()->getAmount() + $this->getChange()->getInKg()->getAmount(), 'kg');
	}

	public function getDifference(&$calculator)
	{
		if ($this->getVector() instanceof Vectors\Loose) {
			return $this->getFinal($calculator)->getInKg()->getAmount() - $this->getWeight()->getInKg()->getAmount();
		} elseif ($this->getVector() instanceof Vectors\Gain) {
			return $this->getWeight()->getInKg()->getAmount() - $this->getFinal($calculator)->getInKg()->getAmount();
		}
	}

	public function getGoalTdee($calculator)
	{
		$vector = $this->getVector();
		if (!($vector instanceof Vector)) {
			throw (new FattyException("Missing goal vector."))
				->setAbbr('missingGoalvector')
				;
		}

		return $vector->getGoalTdee($calculator);
	}
}
