<?php

namespace Fatty;

use Exception;

class SportDurations
{
	const DEFAULT_PAL = .03;

	private $aerobic;
	private $anaerobic;
	private $lowFrequency;

	public function setLowFrequency($sportDuration)
	{
		if (!($sportDuration instanceof SportDurations\LowFrequency)) {
			try {
				$sportDuration = new SportDurations\LowFrequency($sportDuration);
			} catch (\Exception $e) {
				throw (new Exceptions\CaloricCalculatorException("Invalid low frequency sport duration."))
					->setAbbr('invalidLowFrequencySportDuration')
					;
			}
		}

		$this->lowFrequency = $sportDuration;

		return $this;
	}

	public function getLowFrequency()
	{
		return $this->lowFrequency;
	}

	public function setAerobic($sportDuration)
	{
		if (!($sportDuration instanceof SportDurations\Aerobic)) {
			try {
				$sportDuration = new SportDurations\Aerobic($sportDuration);
			} catch (\Exception $e) {
				throw (new Exceptions\CaloricCalculatorException("Invalid aerobic sport duration."))
					->setAbbr('invalidAerobicSportDuration')
					;
			}
		}

		$this->aerobic = $sportDuration;

		return $this;
	}

	public function getAerobic()
	{
		return $this->aerobic;
	}

	public function setAnaerobic($sportDuration)
	{
		if (!($sportDuration instanceof SportDurations\Anaerobic)) {
			try {
				$sportDuration = new SportDurations\Anaerobic($sportDuration);
			} catch (\Exception $e) {
				throw (new Exceptions\CaloricCalculatorException("Invalid anaerobic sport duration."))
					->setAbbr('invalidAnaerobicSportDuration')
					;
			}
		}

		$this->anaerobic = $sportDuration;

		return $this;
	}

	public function getAnaerobic()
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
