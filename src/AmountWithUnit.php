<?php

namespace Fatty;

abstract class AmountWithUnit
{
	const BASE_UNIT = null;

	protected $amount;
	protected $unit;

	abstract public function getInBaseUnit(): AmountWithUnit;
	abstract public function getInUnit(string $unit): AmountWithUnit;

	public function __construct(Amount $amount, string $unit = null)
	{
		$this->amount = $amount;
		$this->unit = $unit;
	}

	public function __toString(): string
	{
		return $this->getFormatted();
	}

	public static function createFromString(string $value, string $unit): ?AmountWithUnit
	{
		try {
			$amount = Amount::createFromString($value);
			if ($amount) {
				return new static(
					new Amount($amount->getValue()),
					$unit,
				);
			}

			return null;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getAmount(): ?Amount
	{
		return $this->amount;
	}

	public static function getBaseUnit(): string
	{
		return (string)static::BASE_UNIT;
	}

	public function getUnit(): ?string
	{
		return $this->unit;
	}

	/**
	 * @deprecated
	 */
	public function getArray(): array
	{
		return [
			'amount' => $this->getAmount()->getValue(),
			'unit' => $this->getUnit(),
		];
	}

	public function getFormatted(): string
	{
		return implode(' ', [
			\Katu\Utils\Formatter::getLocalReadableNumber(\Katu\Utils\Formatter::getPreferredLocale(), $this->getAmount()->getValue()),
			$this->getUnit(),
		]);
	}
}
