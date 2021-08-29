<?php

namespace Fatty;

abstract class WeightVector
{
	const LABEL_INFINITIVE = null;
	const TDEE_QUOTIENT = null;
	const WEIGHT_CHANGE_PER_WEEK = null;

	abstract public function getTdeeChangePerDay(&$calculator);

	public function __toString()
	{
		return $this->getLabelInfinitive();
	}

	public function getCode()
	{
		return lcfirst(array_slice(explode('\\', get_called_class()), -1, 1)[0]);
	}

	public function getTdeeQuotient(&$calculator)
	{
		return static::TDEE_QUOTIENT;
	}

	public function getChangePerWeek()
	{
		return new Weight(static::WEIGHT_CHANGE_PER_WEEK, 'kg');
	}

	public function getGoalTdee(&$calculator)
	{
		return new Energy($calculator->getTotalDailyEnergyExpenditure()->getAmount() + $this->getTdeeChangePerDay($calculator)->getAmount(), 'kCal');
	}

	public function getLabelInfinitive()
	{
		return static::LABEL_INFINITIVE;
	}
}