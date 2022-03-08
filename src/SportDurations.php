<?php

namespace Fatty;

use Fatty\Metrics\AmountMetric;
use Fatty\SportDurations\Aerobic;
use Fatty\SportDurations\Anaerobic;
use Fatty\SportDurations\LowFrequency;

class SportDurations
{
	const DEFAULT_PAL = .03;

	private $aerobic;
	private $anaerobic;
	private $lowFrequency;

	public function setLowFrequency(?LowFrequency $value): SportDurations
	{
		$this->lowFrequency = $value;

		return $this;
	}

	public function getLowFrequency(): ?SportDuration
	{
		return $this->lowFrequency;
	}

	public function setAerobic(?Aerobic $value): SportDurations
	{
		$this->aerobic = $value;

		return $this;
	}

	public function getAerobic(): ?SportDuration
	{
		return $this->aerobic;
	}

	public function setAnaerobic(?Anaerobic $value): SportDurations
	{
		$this->anaerobic = $value;

		return $this;
	}

	public function getAnaerobic(): ?SportDuration
	{
		return $this->anaerobic;
	}

	public function calcSportActivity(): AmountMetric
	{
		$amount = 0;

		if ($this->lowFrequency || $this->aerobic || $this->anaerobic) {
			if ($this->lowFrequency) {
				$amount += $this->getLowFrequency()->getActivity()->getValue();
			}

			if ($this->aerobic) {
				$amount += $this->getAerobic()->getActivity()->getValue();
			}

			if ($this->anaerobic) {
				$amount += $this->getAnaerobic()->getActivity()->getValue();
			}
		} else {
			$amount = static::DEFAULT_PAL;
		}

		return new AmountMetric("sportActivity", new Activity($amount));
	}

	public function getTotalDuration(): Duration
	{
		return new Duration(new Amount(array_sum([
			$this->getLowFrequency() instanceof SportDuration ? $this->getLowFrequency()->getAmount()->getValue() : 0,
			$this->getAerobic() instanceof SportDuration ? $this->getAerobic()->getAmount()->getValue() : 0,
			$this->getAnaerobic() instanceof SportDuration ? $this->getAnaerobic()->getAmount()->getValue() : 0,
		])), "minutesPerWeek");
	}

	public function getUtilizedDurations(): array
	{
		return array_values(array_filter([
			$this->getLowFrequency() instanceof SportDuration && $this->getLowFrequency()->getAmount()->getValue() ? $this->getLowFrequency() : null,
			$this->getAerobic() instanceof SportDuration && $this->getAerobic()->getAmount()->getValue() ? $this->getAerobic() : null,
			$this->getAnaerobic() instanceof SportDuration && $this->getAnaerobic()->getAmount()->getValue() ? $this->getAnaerobic() : null,
		]));
	}

	public function getMaxDuration(): ?Duration
	{
		$amount = max(array_map(function (SportDuration $i) {
			return $i->getAmount()->getValue();
		}, $this->getUtilizedDurations()));

		if ($amount) {
			return new Duration(new Amount($amount), "minutesPerWeek");
		}

		return null;
	}

	public function getMaxDurations(): array
	{
		return array_values(array_filter(array_map(function ($i) {
			return $i->getAmount() == $this->getMaxDuration()->getAmount() ? $i : null;
		}, $this->getUtilizedDurations())));
	}
}
