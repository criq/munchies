<?php

namespace Fatty;

class AmountWithUnit
{
	protected $amount;
	protected $unit;

	public function __construct(Amount $amount, string $unit = null)
	{
		$this->amount = $amount;
		$this->unit = $unit;
	}

	public function __toString() : string
	{
		return implode(' ', [
			\Katu\Utils\Formatter::getLocalReadableNumber(\Katu\Utils\Formatter::getPreferredLocale(), $this->getAmount()),
			$this->getUnit(),
		]);
	}

	public static function createFromString(string $value) : ?AmountWithUnit
	{
		try {
			$amount = Amount::createFromString($value);
			if ($amount) {
				return new static($amount);
			}

			return null;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getAmount() : ?Amount
	{
		return $this->amount;
	}

	public function getUnit() : ?string
	{
		return $this->unit;
	}

	public function getArray() : array
	{
		return [
			'amount' => $this->getAmount()->getValue(),
			'unit' => $this->getUnit(),
		];
	}
}
