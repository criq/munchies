<?php

namespace Fatty;

use Fatty\Exceptions\MissingHeightException;
use Fatty\Metrics\QuantityMetric;

class Proportions
{
	private $height;
	private $hips;
	private $neck;
	private $waist;

	/*****************************************************************************
	 * Výška
	 */
	public function setHeight(?Length $length): Proportions
	{
		$this->height = $length;

		return $this;
	}

	public function getHeight(): ?Length
	{
		return $this->height;
	}

	public function calcHeight(): QuantityMetric
	{
		$height = $this->getHeight();
		if (!$height) {
			throw new MissingHeightException;
		}

		$heightValue = $height->getAmount()->getValue();

		$formula = "height[{$heightValue}] = {$heightValue}";

		return new QuantityMetric('height', $height, $formula);
	}

	/*****************************************************************************
	 * Obvod pasu.
	 */
	public function setWaist(?Length $length): Proportions
	{
		$this->waist = $length;

		return $this;
	}

	public function getWaist(): ?Length
	{
		return $this->waist;
	}

	/*****************************************************************************
	 * Obvod boků.
	 */
	public function setHips(?Length $length): Proportions
	{
		$this->hips = $length;

		return $this;
	}

	public function getHips(): ?Length
	{
		return $this->hips;
	}

	/*****************************************************************************
	 * Obvod krku.
	 */
	public function setNeck(?Length $length): Proportions
	{
		$this->neck = $length;

		return $this;
	}

	public function getNeck(): ?Length
	{
		return $this->neck;
	}
}
