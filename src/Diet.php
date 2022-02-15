<?php

namespace Fatty;

use Fatty\Exceptions\InvalidDietCarbsException;
use Fatty\Exceptions\MissingDietApproachException;
use Fatty\Metrics\StringMetric;
use Fatty\Nutrients\Carbs;

class Diet
{
	protected $approach;
	protected $carbs;
	protected $dateTimeStart;
	protected $weightHistory;

	public function __construct(?Approach $approach = null)
	{
		$this->setApproach($approach);
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

	public function calcDietApproach(): StringMetric
	{
		$approach = $this->getApproach();
		if (!$approach) {
			throw new MissingDietApproachException;
		}

		return new StringMetric("dietApproach", $approach->getCode(), $approach->getDeclinatedLabel());
	}

	public function setDateTimeStart(?\DateTime $value): Diet
	{
		$this->dateTimeStart = $value;

		return $this;
	}

	public function getDateTimeStart(): ?\DateTime
	{
		return $this->dateTimeStart;
	}

	public function setWeightHistory(?WeightHistory $value): Diet
	{
		$this->weightHistory = $value;

		return $this;
	}

	public function getWeightHistory(): WeightHistory
	{
		return $this->weightHistory ?: new WeightHistory;
	}
}
