<?php

namespace Fatty;

use Fatty\SportDurations\Aerobic;
use Fatty\SportDurations\Anaerobic;
use Fatty\SportDurations\LowFrequency;

class SportDurations
{
	const DEFAULT_PAL = .03;

	private $aerobic;
	private $anaerobic;
	private $lowFrequency;

	public function setLowFrequency(?LowFrequency $value) : SportDurations
	{
		$this->lowFrequency = $value;

		return $this;
	}

	public function getLowFrequency() : ?SportDuration
	{
		return $this->lowFrequency;
	}

	public function setAerobic(?Aerobic $value) : SportDurations
	{
		$this->aerobic = $value;

		return $this;
	}

	public function getAerobic() : ?SportDuration
	{
		return $this->aerobic;
	}

	public function setAnaerobic(?Anaerobic $value) : SportDurations
	{
		$this->anaerobic = $value;

		return $this;
	}

	public function getAnaerobic() : ?SportDuration
	{
		return $this->anaerobic;
	}

	public function calcSportActivity() : Activity
	{
		$amount = 0;

		if ($this->lowFrequency || $this->aerobic || $this->anaerobic) {
			if ($this->lowFrequency) {
				$amount += $this->lowFrequency->getActivityAmount()->getValue();
			}

			if ($this->aerobic) {
				$amount += $this->aerobic->getActivityAmount()->getValue();
			}

			if ($this->anaerobic) {
				$amount += $this->anaerobic->getActivityAmount()->getValue();
			}
		} else {
			$amount = static::DEFAULT_PAL;
		}

		return new Activity($amount);
	}

	public function getTotalDuration()
	{
		return array_sum([
			$this->lowFrequency instanceof SportDuration ? $this->lowFrequency->getAmount() : 0,
			$this->aerobic instanceof SportDuration ? $this->aerobic->getAmount() : 0,
			$this->anaerobic instanceof SportDuration ? $this->anaerobic->getAmount() : 0,
		]);
	}

	public function getUtilizedDurations()
	{
		return array_values(array_filter([
			$this->lowFrequency instanceof SportDuration && $this->lowFrequency->getAmount() ? $this->lowFrequency : null,
			$this->aerobic instanceof SportDuration && $this->aerobic->getAmount() ? $this->aerobic : null,
			$this->anaerobic instanceof SportDuration && $this->anaerobic->getAmount() ? $this->anaerobic : null,
		]));
	}

	public function getMaxDuration()
	{
		$amount = max(array_map(function ($i) {
			return $i->getAmount();
		}, $this->getUtilizedDurations()));

		if ($amount) {
			return new Duration($amount, 'minutesPerWeek');
		}

		return false;
	}

	public function getMaxDurations()
	{
		return array_values(array_filter(array_map(function ($i) {
			return $i->getAmount() == $this->getMaxDuration()->getAmount() ? $i : null;
		}, $this->getUtilizedDurations())));
	}
}
