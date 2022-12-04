<?php

namespace Fatty;

use Fatty\Exceptions\PregnancyChildbirthDateInPastException;
use Fatty\Pregnancy\Week;
use Fatty\Pregnancy\WeekCollection;
use Fatty\Pregnancy\Trimester;
use Fatty\Pregnancy\TrimesterCollection;
use Katu\Tools\Calendar\Interval;
use Katu\Tools\Calendar\Time;

class Pregnancy
{
	public function setChildbirthDate(?Time $date): Pregnancy
	{
		if (is_null($date)) {
			$this->childbirthDate = null;
		} else {
			if ($date < new Time()) {
				throw new PregnancyChildbirthDateInPastException;
			}

			$this->childbirthDate = $date;
		}

		return $this;
	}

	public function getChildbirthDate(): ?Time
	{
		return $this->childbirthDate;
	}

	public function getConceptionDate(): ?Time
	{
		return (clone $this->getChildbirthDate())->modify("- 280 days")->setTime(0, 0, 0, 0);
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

	public function getIsPregnant(): bool
	{
		try {
			return $this->getChildbirthDate() > new Time;
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
				(clone $this->getConceptionDate())->modify("+ 280 days"),
			)),
		]);
	}
}
