<?php

namespace Fatty;

use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\SportActivityMetric;
use Fatty\SportDurations\Aerobic;
use Fatty\SportDurations\Anaerobic;
use Fatty\SportDurations\LowFrequency;
use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

class SportDurations
{
	const DEFAULT_PAL = .03;

	private $aerobic;
	private $anaerobic;
	private $lowFrequency;

	public static function validateLowFrequency(Param $lowFrequency): Validation
	{
		$output = LowFrequency::createFromString($lowFrequency, "minutesPerWeek");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid amount of low frequency activity."))->addParam($lowFrequency));
		} else {
			return (new Validation)->setResponse($output)->addParam($lowFrequency->setOutput($output));
		}
	}

	public function setLowFrequency(?LowFrequency $value): SportDurations
	{
		$this->lowFrequency = $value;

		return $this;
	}

	public function getLowFrequency(): ?SportDuration
	{
		return $this->lowFrequency;
	}

	public static function validateAerobic(Param $aerobic): Validation
	{
		$output = Aerobic::createFromString($aerobic, "minutesPerWeek");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid amount of aerobic activity."))->addParam($aerobic));
		} else {
			return (new Validation)->setResponse($output)->addParam($aerobic->setOutput($output));
		}
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

	public static function validateAnaerobic(Param $anaerobic): Validation
	{
		$output = Anaerobic::createFromString($anaerobic, "minutesPerWeek");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid amount of anaerobic activity."))->addParam($anaerobic));
		} else {
			return (new Validation)->setResponse($output)->addParam($anaerobic->setOutput($output));
		}
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

	public function calcSportActivity(): AmountMetricResult
	{
		$result = new AmountMetricResult(new SportActivityMetric);

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

		$result->setResult(new Activity($amount));

		return $result;
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
		return array_values(array_filter(array_map(function (SportDuration $i) {
			return $i->getAmount()->getValue() == $this->getMaxDuration()->getAmount()->getValue() ? $i : null;
		}, $this->getUtilizedDurations())));
	}

	public function getMaxProteinSportDuration(): ?SportDuration
	{
		if ($this->getAnaerobic() && $this->getAnaerobic()->getAmount()->getValue() >= 60) {
			return $this->getAnaerobic();
		}

		if ($this->getAerobic() && $this->getAerobic()->getAmount()->getValue() >= 60) {
			return $this->getAerobic();
		}

		if ($this->getLowFrequency() && $this->getLowFrequency()->getAmount()->getValue() >= 60) {
			return $this->getLowFrequency();
		}

		return null;
	}
}
