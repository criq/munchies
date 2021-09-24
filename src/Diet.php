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

	public function setApproach(?Approach $value): Diet
	{
		$this->approach = $value;

		return $this;
	}

	public function getApproach(): ?Approach
	{
		return $this->approach;
	}

	public function calcDietApproach(): StringMetric
	{
		$approach = $this->getApproach();
		if (!$approach) {
			throw new MissingDietApproachException;
		}

		return new StringMetric('dietApproach', $approach->getCode(), $approach->getLabelDeclinated());
	}

	public function setCarbs(Carbs $carbs): Diet
	{
		if ($this->getApproach() && $this->getApproach()->getCarbsMin() && $this->getApproach()->getCarbsMax() && ($carbs->getAmount() < $this->getApproach()->getCarbsMin() || $carbs->getAmount() > $this->getApproach()->getCarbsMax())) {
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

		return $this->getApproach()->getCarbsDefault();
	}
}
