<?php

namespace Fatty;

use Fatty\Errors\MissingDietApproachError;
use Fatty\Exceptions\InvalidDietCarbsException;
use Fatty\Exceptions\MissingDietApproachException;
use Fatty\Metrics\DietApproachMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Metrics\StringMetricResult;
use Fatty\Nutrients\Carbs;
use Katu\Errors\Error;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

class Diet
{
	protected $approach;
	protected $carbs;
	protected $timeStart;

	public function __construct(?Approach $approach = null)
	{
		$this->setApproach($approach);
	}

	public static function validateApproach(Param $approach): Validation
	{
		$output = Approach::createFromCode($approach);
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid diet approach."))->addParam($approach));
		} else {
			return (new Validation)->setResponse($output)->addParam($approach->setOutput($output));
		}
	}

	public function setApproach(?Approach $value): Diet
	{
		$this->approach = $value;

		return $this;
	}

	public function getApproach(): ?Approach
	{
		return $this->approach;
	}

	public static function validateCarbs(Param $carbs): Validation
	{
		$output = Carbs::createFromString($carbs, "g");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid diet carbs."))->addParam($carbs));
		} else {
			return (new Validation)->setResponse($output)->addParam($carbs->setOutput($output));
		}
	}

	public function setCarbs(Carbs $carbs): Diet
	{
		if ($this->getApproach() && $this->getApproach()->getMinCarbs() && $this->getApproach()->getMaxCarbs() && ($carbs->getAmount() < $this->getApproach()->getMinCarbs() || $carbs->getAmount() > $this->getApproach()->getMaxCarbs())) {
			throw new InvalidDietCarbsException;
		}

		$this->carbs = $carbs;

		return $this;
	}

	public function getCarbs(): Carbs
	{
		if ($this->carbs) {
			return $this->carbs;
		}

		$approach = $this->getApproach();
		if (!$approach) {
			throw new MissingDietApproachException;
		}

		return $this->getApproach()->getDefaultCarbs();
	}

	public function calcDietApproach(): StringMetricResult
	{
		$result = new StringMetricResult(new DietApproachMetric);

		$approach = $this->getApproach();
		if (!$approach) {
			$result->addError(new MissingDietApproachError);
		} else {
			$result->setResult(new StringValue($approach->getCode()));
		}

		return $result;
	}

	public function setTimeStart(?Time $timeStart): Diet
	{
		$this->timeStart = $timeStart;

		return $this;
	}

	public function getTimeStart(): ?Time
	{
		return $this->timeStart;
	}

	public function getDayIndex(Calculator $calculator): ?int
	{
		try {
			$diff = $calculator->getDiet()->getTimeStart()->diff($calculator->getReferenceTime());
			if ($diff->invert) {
				return null;
			}

			return $diff->days + 1;
		} catch (\Throwable $e) {
			return null;
		}
	}
}
