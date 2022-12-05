<?php

namespace Fatty;

use Fatty\Pregnancy\Week;
use Fatty\Pregnancy\WeekCollection;
use Fatty\Pregnancy\Trimester;
use Fatty\Pregnancy\TrimesterCollection;
use Katu\Tools\Calendar\Interval;
use Katu\Tools\Calendar\Time;

class Pregnancy
{
	public function setChildbirthDate(?Time $time): Pregnancy
	{
		if (is_null($time)) {
			$this->childbirthDate = null;
		} else {
			$this->childbirthDate = $time;
		}

		return $this;
	}

	public function getConceptionDate(): ?Time
	{
		return (clone $this->getChildbirthDate())->modify("- 280 days")->setTime(0, 0, 0, 0);
	}

	public function getChildbirthDate(): ?Time
	{
		return $this->childbirthDate;
	}

	public function getInterval(): ?Interval
	{
		try {
			return new Interval($this->getConceptionDate(), $this->getChildbirthDate());
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return null;
	}

	public function setWeightBeforePregnancy(?Weight $weight): Pregnancy
	{
		$this->weightBeforePregnancy = $weight;

		return $this;
	}

	public function getWeightBeforePregnancy(): ?Weight
	{
		return $this->weightBeforePregnancy;
	}

	public function getIsPregnant(Time $referenceDate): bool
	{
		try {
			return $this->getInterval()->fitsTime($referenceDate);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	public function getWeeks(): WeekCollection
	{
		$res = new WeekCollection;
		for ($index = 1; $index <= 42; $index++) {
			$modifier = ($index - 1) * 7;
			$start = (clone $this->getConceptionDate())->modify("+ {$modifier} days");
			$end = (clone $start)->modify("+ 7 days");

			$res[] = new Week($index, new Interval($start, $end));
		}

		return $res;
	}

	public function getTrimesters(): TrimesterCollection
	{
		return new TrimesterCollection([
			new Trimester(1, new Interval(
				(clone $this->getConceptionDate()),
				(clone $this->getConceptionDate())->modify("+ 94 days"),
			)),
			new Trimester(2, new Interval(
				(clone $this->getConceptionDate())->modify("+ 94 days"),
				(clone $this->getConceptionDate())->modify("+ 187 days"),
			)),
			new Trimester(3, new Interval(
				(clone $this->getConceptionDate())->modify("+ 187 days"),
				(clone $this->getConceptionDate())->modify("+ 294 days"),
			)),
		]);
	}

	public function getCurrentWeek(Time $referenceDate): ?Week
	{
		foreach ($this->getWeeks() as $week) {
			if ($week->getInterval()->fitsTime($referenceDate)) {
				return $week;
			}
		}

		return null;
	}

	public function getCurrentTrimester(Time $referenceDate): ?Trimester
	{
		foreach ($this->getTrimesters() as $trimester) {
			if ($trimester->getInterval()->fitsTime($referenceDate)) {
				return $trimester;
			}
		}

		return null;
	}
}
