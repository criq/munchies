<?php

namespace Fatty;

class Goal
{
	private $duration;
	private $direction;
	private $weight;

	public function setDirection($weightVector)
	{
		if (is_string($weightVector)) {
			$className = "Fatty\\WeightVectors\\" . ucfirst($weightVector);
			if (class_exists($className)) {
				$weightVector = new $className;
			}
		}

		if (!($weightVector instanceof WeightVector)) {
			throw (new CaloricCalculatorException("Invalid goal direction."))
				->setAbbr('invalidGoaldirection')
				;
		}

		$this->direction = $weightVector;

		return $this;
	}

	public function getDirection()
	{
		return $this->direction;
	}

	public function setWeight($weight)
	{
		if (!($weight instanceof Weight)) {
			try {
				$weight = new Weight($weight);
			} catch (\Fatty\Exceptions\InvalidAmountException $e) {
				throw (new CaloricCalculatorException("Invalid goal weight."))
					->setAbbr('invalidGoalWeight')
					;
			}
		}

		$this->weight = $weight;

		return $this;
	}

	public function getWeight()
	{
		return $this->weight;
	}

	public function setDuration($duration)
	{
		if (!($duration instanceof Duration)) {
			throw (new CaloricCalculatorException("Invalid goal duration."))
				->setAbbr('invalidGoalDuration')
				;
		}

		$this->duration = $duration;

		return $this;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	public function getChange()
	{
		return new Weight($this->getDirection()->getChangePerWeek()->getInKg()->getAmount() * $this->getDuration()->getInWeeks()->getAmount());
	}

	public function getFinal(&$calculator)
	{
		$weight = $this->getWeight();
		if (!($weight instanceof Weight)) {
			throw (new CaloricCalculatorException("Missing weight."))
				->setAbbr('missingWeight')
				;
		}

		return new Weight($weight->getInKg()->getAmount() + $this->getChange()->getInKg()->getAmount(), 'kg');
	}

	public function getDifference(&$calculator)
	{
		if ($this->getDirection() instanceof WeightVectors\Loose) {
			return $this->getFinal($calculator)->getInKg()->getAmount() - $this->getWeight()->getInKg()->getAmount();
		} elseif ($this->getDirection() instanceof WeightVectors\Gain) {
			return $this->getWeight()->getInKg()->getAmount() - $this->getFinal($calculator)->getInKg()->getAmount();
		}
	}

	public function getGoalTdee($calculator)
	{
		$direction = $this->getDirection();
		if (!($direction instanceof WeightVector)) {
			throw (new CaloricCalculatorException("Missing goal direction."))
				->setAbbr('missingGoaldirection')
				;
		}

		return $direction->getGoalTdee($calculator);
	}
}
