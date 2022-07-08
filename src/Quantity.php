<?php

namespace Fatty;

abstract class Quantity implements \Effekt\QuantityInterface
{
	const BASE_UNIT = null;

	protected $amount;
	protected $unit;

	abstract public function getInBaseUnit(): Quantity;
	abstract public function getInUnit(string $unit): Quantity;

	public function __construct(Amount $amount, string $unit = null)
	{
		$this->amount = $amount;
		$this->unit = $unit;
	}

	public function __toString(): string
	{
		return $this->getFormatted();
	}

	public static function createFromString(string $value, string $unit): ?Quantity
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

	public function getAmountFloat(): float
	{
		return $this->getAmount()->getValue();
	}

	public static function getBaseUnit(): string
	{
		return (string)static::BASE_UNIT;
	}

	public function getUnit(): ?string
	{
		return $this->unit;
	}

	public function getUnitString(): string
	{
		return $this->getUnit();
	}

	/**
	 * @deprecated
	 */
	public function getArray(): array
	{
		return [
			"amount" => $this->getAmount()->getValue(),
			"unit" => $this->getUnit(),
		];
	}

	public function getFormatted(?Locale $locale = null): string
	{
		$locale = $locale ?: Locale::getDefault();

		$numberFormatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
		$numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 1);

		return implode(" ", [
			$numberFormatter->format($this->getAmount()->getValue()),
			$this->getUnit(),
		]);
	}

	public function modify(Quantity $modifier): Quantity
	{
		return new static(new Amount($this->getAmount()->getValue() + $modifier->getInUnit($this->getUnit())->getAmount()->getValue()), $this->getUnit());
	}
}
