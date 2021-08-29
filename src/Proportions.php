<?php

namespace Fatty;

class Proportions
{
	private $height;
	private $hips;
	private $neck;
	private $waist;

	/*****************************************************************************
	 * Výška
	 */
	public function setHeight($length)
	{
		if (!($length instanceof Length)) {
			try {
				$length = new Length($length);
			} catch (\Fatty\Exceptions\InvalidAmountException $e) {
				throw (new \Fatty\Exceptions\CaloricCalculatorException("Invalid height."))
					->setAbbr('invalidHeightAmount')
					;
			}
		}

		$this->height = $length;
	}

	public function getHeight()
	{
		return $this->height;
	}

	/*****************************************************************************
	 * Obvod pasu.
	 */
	public function setWaist($length)
	{
		if (!($length instanceof Length)) {
			try {
				$length = new Length($length);
			} catch (\Fatty\Exceptions\InvalidAmountException $e) {
				throw (new \Fatty\Exceptions\CaloricCalculatorException("Invalid waist."))
					->setAbbr('invalidWaistAmount')
					;
			}
		}

		$this->waist = $length;
	}

	public function getWaist()
	{
		return $this->waist;
	}

	/*****************************************************************************
	 * Obvod boků.
	 */
	public function setHips($length)
	{
		if (!($length instanceof Length)) {
			try {
				$length = new Length($length);
			} catch (\Fatty\Exceptions\InvalidAmountException $e) {
				throw (new \Fatty\Exceptions\CaloricCalculatorException("Invalid hips."))
					->setAbbr('invalidHipsAmount')
					;
			}
		}

		$this->hips = $length;
	}

	public function getHips()
	{
		return $this->hips;
	}

	/*****************************************************************************
	 * Obvod krku.
	 */
	public function setNeck($length)
	{
		if (!($length instanceof Length)) {
			try {
				$length = new Length($length);
			} catch (\Fatty\Exceptions\InvalidAmountException $e) {
				throw (new \Fatty\Exceptions\CaloricCalculatorException("Invalid neck."))
					->setAbbr('invalidNeckAmount')
					;
			}
		}

		$this->neck = $length;
	}

	public function getNeck()
	{
		return $this->neck;
	}
}
