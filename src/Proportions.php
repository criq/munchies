<?php

namespace Fatty;

use Fatty\Exceptions\MissingHeightException;
use Fatty\Metrics\QuantityMetric;
use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

class Proportions
{
	private $height;
	private $hips;
	private $neck;
	private $waist;

	/*****************************************************************************
	 * Výška
	 */
	public static function validateHeight(Param $height): Validation
	{
		$output = Length::createFromString($height, "cm");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid height."))->addParam($height));
		} else {
			return (new Validation)->setResponse($output)->addParam($height->setOutput($output));
		}
	}

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
	public static function validateWaist(Param $waist): Validation
	{
		$output = Length::createFromString($waist, "cm");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid waist."))->addParam($waist));
		} else {
			return (new Validation)->setResponse($output)->addParam($waist->setOutput($output));
		}
	}

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
	public static function validateHips(Param $hips): Validation
	{
		$output = Length::createFromString($hips, "cm");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid hips."))->addParam($hips));
		} else {
			return (new Validation)->setResponse($output)->addParam($hips->setOutput($output));
		}
	}

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
	public static function validateNeck(Param $neck): Validation
	{
		$output = Length::createFromString($neck, "cm");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid neck."))->addParam($neck));
		} else {
			return (new Validation)->setResponse($output)->addParam($neck->setOutput($output));
		}
	}

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
