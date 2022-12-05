<?php

namespace Fatty;

use Fatty\Metrics\QuantityMetric;
use Katu\Tools\Calendar\Timeout;

class ChildCollection extends \ArrayObject
{
	public function filterYoungerThan(Timeout $timeout): ChildCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Child $child) use ($timeout) {
			return $timeout->fits($child->getBirthday()->getTime());
		})));
	}

	public function calcReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetric
	{
		$energy = new Energy(new Amount, "kJ");

		foreach (array_map(function (Child $child) use ($calculator) {
			return $child->calcReferenceDailyIntakeBonus($calculator)->getResult();
		}, $this->getArrayCopy()) as $childEnergy) {
			$energy->modify($childEnergy);
		}

		return new QuantityMetric(
			"referenceDailyIntakeBonus",
			$energy,
		);
	}
}
