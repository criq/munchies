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

	public function getActivityAmount()
	{
		$pal = 0;

		if ($this->lowFrequency || $this->aerobic || $this->anaerobic) {
			if ($this->lowFrequency) {
				$pal += $this->lowFrequency->getActivityAmount()->getAmount();
			}

			if ($this->aerobic) {
				$pal += $this->aerobic->getActivityAmount()->getAmount();
			}

			if ($this->anaerobic) {
				$pal += $this->anaerobic->getActivityAmount()->getAmount();
			}
		} else {
			$pal = static::DEFAULT_PAL;
		}

		return new ActivityAmount($pal);
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
