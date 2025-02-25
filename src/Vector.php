<?php

namespace Fatty;

use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\WeightGoalQuotientMetric;

abstract class Vector
{
	const CODE = "";
	const LABEL_INFINITIVE = "";
	const WEIGHT_CHANGE_PER_WEEK = "";
	const WEIGHT_GOAL_QUOTIENT = null;

	public function __toString()
	{
		return (string)$this->getLabelInfinitive();
	}

	public static function createFromCode(string $value): ?Vector
	{
		return static::getAvailableClasses()[$value] ?? null;
	}

	public static function getAvailableClasses(): array
	{
		return [
			\Fatty\Vectors\Gain::CODE => new \Fatty\Vectors\Gain,
			\Fatty\Vectors\Loose::CODE => new \Fatty\Vectors\Loose,
			\Fatty\Vectors\Maintain::CODE => new \Fatty\Vectors\Maintain,
			\Fatty\Vectors\SlowGain::CODE => new \Fatty\Vectors\SlowGain,
			\Fatty\Vectors\SlowLoose::CODE => new \Fatty\Vectors\SlowLoose,
		];
	}

	public function getCode(): string
	{
		return static::CODE;
	}

	public function calcWeightGoalQuotient(Calculator $calculator): AmountMetricResult
	{
		return (new AmountMetricResult(new WeightGoalQuotientMetric))
			->setResult(new Amount((float)static::WEIGHT_GOAL_QUOTIENT))
			;
	}

	public function getChangePerWeek()
	{
		return new Weight(
			new Amount(static::WEIGHT_CHANGE_PER_WEEK),
			"kg",
		);
	}

	public function getLabelInfinitive(): string
	{
		return static::LABEL_INFINITIVE;
	}
}
