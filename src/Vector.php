<?php

namespace Fatty;

use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\AmountWithUnitMetric;

abstract class Vector
{
	const LABEL_INFINITIVE = null;
	const TDEE_QUOTIENT = null;
	const WEIGHT_CHANGE_PER_WEEK = '';

	abstract public function calcTdeeChangePerDay(Calculator $calculator): AmountWithUnitMetric;

	public function __toString()
	{
		return (string)$this->getLabelInfinitive();
	}

	public static function createFromString(string $value)
	{
		try {
			$class = 'Fatty\\Vectors\\' . ucfirst($value);

			return new $class;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getCode()
	{
		return lcfirst(array_slice(explode('\\', get_called_class()), -1, 1)[0]);
	}

	public function calcTdeeQuotient(Calculator $calculator): AmountMetric
	{
		return new AmountMetric('tdeeQuotient', new Amount((float)static::TDEE_QUOTIENT));
	}

	public function getChangePerWeek()
	{
		return new Weight(
			new Amount(static::WEIGHT_CHANGE_PER_WEEK),
			'kg',
		);
	}

	public function calcGoalWeightGoalEnergyExpenditure(Calculator $calculator): AmountWithUnitMetric
	{
		$weightGoalEnergyExpenditureValue = $calculator->calcWeightGoalEnergyExpenditure()->getResult()->getInBaseUnit()->getAmount()->getValue();
		$tdeeChangePerDayValue = $this->calcTdeeChangePerDay($calculator)->getResult()->getInBaseUnit()->getAmount()->getValue();

		$result = new Energy(
			new Amount(
				$weightGoalEnergyExpenditureValue + $tdeeChangePerDayValue
			),
			Energy::getBaseUnit(),
		);

		return new AmountWithUnitMetric('goalWeightGoalEnergyExpenditure', $result);
	}

	public function getLabelInfinitive()
	{
		return static::LABEL_INFINITIVE;
	}
}
