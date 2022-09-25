<?php

namespace Fatty;

use Fatty\Exceptions\MissingGoalDurationException;
use Fatty\Exceptions\MissingGoalVectorException;
use Fatty\Exceptions\MissingGoalWeightException;
use Fatty\Exceptions\MissingWeightException;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\StringMetric;
use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

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

	public function calcGoalDuration(): QuantityMetric
	{
		$duration = $this->getDuration();
		if (!$duration) {
			throw new MissingGoalDurationException;
		}

		return new QuantityMetric("goalDuration", $duration);
	}

	public static function validateVector(Param $vector): Validation
	{
		$output = Vector::createFromCode($vector);
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid goal vector code."))->addParam($vector));
		} else {
			return (new Validation)->setResponse($output)->addParam($vector->setOutput($output));
		}
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

	public static function validateWeight(Param $weight): Validation
	{
		$output = Weight::createFromString($weight, "kg");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid goal weight."))->addParam($weight));
		} else {
			return (new Validation)->setResponse($output)->addParam($weight->setOutput($output));
		}
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
				$this->getVector()->getChangePerWeek()->getInUnit("kg")->getAmount()->getValue() * $this->getDuration()->getInUnit("weeks")->getAmount()->getValue()
			),
			"kg",
		);
	}

	public function getFinal(Calculator $calculator)
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		return new Weight(
			$weight->getInUnit("kg")->getAmount() + $this->getChange()->getInUnit("kg")->getAmount(),
			"kg"
		);
	}

	public function getDifference(Calculator $calculator)
	{
		if ($this->getVector() instanceof Vectors\Loose) {
			return $this->getFinal($calculator)->getInUnit("kg")->getAmount() - $this->getWeight()->getInUnit("kg")->getAmount();
		} elseif ($this->getVector() instanceof Vectors\Gain) {
			return $this->getWeight()->getInUnit("kg")->getAmount() - $this->getFinal($calculator)->getInUnit("kg")->getAmount();
		}
	}

	public function calcGoalVector(): StringMetric
	{
		$vector = $this->getVector();
		if (!$vector) {
			throw new MissingGoalVectorException;
		}

		return new StringMetric("goalVector", $vector->getCode(), $vector->getLabelInfinitive());
	}

	public function calcGoalWeight(): QuantityMetric
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingGoalWeightException;
		}

		return new QuantityMetric("goalWeight", $this->getWeight());
	}

	// public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetric
	// {
	// 	$vector = $this->getVector();
	// 	if (!$vector) {
	// 		throw new MissingGoalVectorException;
	// 	}

	// 	return $vector->calcWeightGoalEnergyExpenditure($calculator);
	// }
}
