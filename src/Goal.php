<?php

namespace Fatty;

use Fatty\Exceptions\MissingGoalVectorException;
use Fatty\Exceptions\MissingWeightException;
use Fatty\Metrics\AmountWithUnitMetric;

class Goal
{
	protected $duration;
	protected $vector;
	protected $weight;

	public function setDuration(?Duration $duration): Goal
	{
		$this->duration = $duration;

		return $this;
	}

	public function getDuration(): ?Duration
	{
		return $this->duration;
	}

	public function setVector(?Vector $value): Goal
	{
		$this->vector = $value;

		return $this;
	}

	public function getVector(): ?Vector
	{
		return $this->vector;
	}

	public function setWeight(?Weight $weight): Goal
	{
		$this->weight = $weight;

		return $this;
	}

	public function getWeight(): ?Weight
	{
		return $this->weight;
	}

	public function getChange()
	{
		return new Weight(
			new Amount(
				$this->getVector()->getChangePerWeek()->getInUnit('kg')->getAmount()->getValue() * $this->getDuration()->getInUnit('weeks')->getAmount()->getValue()
			),
			'kg',
		);
	}

	public function getFinal(Calculator $calculator)
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		return new Weight(
			$weight->getInUnit('kg')->getAmount() + $this->getChange()->getInUnit('kg')->getAmount(),
			'kg'
		);
	}

	public function getDifference(Calculator $calculator)
	{
		if ($this->getVector() instanceof Vectors\Loose) {
			return $this->getFinal($calculator)->getInUnit('kg')->getAmount() - $this->getWeight()->getInUnit('kg')->getAmount();
		} elseif ($this->getVector() instanceof Vectors\Gain) {
			return $this->getWeight()->getInUnit('kg')->getAmount() - $this->getFinal($calculator)->getInUnit('kg')->getAmount();
		}
	}

	public function calcGoalTotalDailyEnergyExpenditure(Calculator $calculator): AmountWithUnitMetric
	{
		$vector = $this->getVector();
		if (!$vector) {
			throw new MissingGoalVectorException;
		}

		return $vector->calcGoalTotalDailyEnergyExpenditure($calculator);
	}
}
