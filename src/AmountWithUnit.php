<?php

namespace Fatty;

class AmountWithUnit extends Amount
{
	protected $unit;

	public function __construct($amount, $unit = null)
	{
		return $this->setAmountWithUnit($amount, $unit);
	}

	public function __toString()
	{
		return implode(' ', [
			\Katu\Utils\Formatter::getLocalReadableNumber(\Katu\Utils\Formatter::getPreferredLocale(), $this->getAmount()),
			$this->getUnit(),
		]);
	}

	public function setAmountWithUnit(float $amount, string $unit) : AmountWithUnit
	{
		if (!static::validateNumber($amount)) {
			throw (new \App\Classes\Profile\Exceptions\InvalidAmountException("Invalid amount."));
		}

		$this->amount = static::sanitizeNumber($amount);
		$this->unit = $unit;

		return $this;
	}

	public function getUnit() : ?string
	{
		return $this->unit;
	}

	public function getArray() : array
	{
		return [
			'amount' => $this->getAmount(),
			'unit' => $this->getUnit(),
		];
	}
}
