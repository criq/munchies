<?php

namespace Fatty;

use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\ReferenceDailyIntakeBonusMetric;
use Katu\Tools\Calendar\Timeout;

class ChildCollection extends \ArrayObject
{
	public function filterYoungerThan(Timeout $timeout): ChildCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Child $child) use ($timeout) {
			return $timeout->fits($child->getBirthday()->getTime());
		})));
	}

	public function filterBreastfed(): ChildCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Child $child) {
			return $child->getIsBreastfed();
		})));
	}

	public function calcReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new ReferenceDailyIntakeBonusMetric);

		$energy = new Energy(new Amount, "kJ");

		foreach ($this as $child) {
			$referenceDailyIntakeBonusResult = $child->calcReferenceDailyIntakeBonus($calculator);
			$result->addErrors($referenceDailyIntakeBonusResult->getErrors());

			if (!$referenceDailyIntakeBonusResult->hasErrors()) {
				$energy->modify($referenceDailyIntakeBonusResult->getResult());
			}
		}

		$result->setResult($energy);

		return $result;
	}
}
