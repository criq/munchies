<?php

namespace Fatty;

use Fatty\Metrics\ResultInterface;
use Psr\Http\Message\ServerRequestInterface;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;

abstract class Quantity implements \Effekt\QuantityInterface, ResultInterface
{
	const BASE_UNIT = null;

	protected $amount;
	protected $unit;

	abstract public function getInBaseUnit(): Quantity;
	abstract public function getInUnit(string $unit): Quantity;

	public function __construct(Amount $amount, string $unit = null)
	{
		$this->setAmount($amount);
		$this->setUnit($unit);
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

	public function setAmount(Amount $amount): Quantity
	{
		$this->amount = $amount;

		return $this;
	}

	public function getAmount(): Amount
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

	public function setUnit(?string $unit): Quantity
	{
		$this->unit = $unit;

		return $this;
	}

	public function getUnit(): ?string
	{
		return $this->unit;
	}

	public function getUnitString(): string
	{
		return $this->getUnit();
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

	// Mutable.
	public function modify(Quantity $modifier): Quantity
	{
		$this->setAmount(new Amount($this->getAmount()->getValue() + $modifier->getInUnit($this->getUnit())->getAmount()->getValue()));

		return $this;
	}

	// Immutable.
	public function getModified(Quantity $modifier): Quantity
	{
		return (clone $this)->modify($modifier);
	}

	public function getNumericalValue(): ?float
	{
		return $this->getAmount()->getNumericalValue();
	}

	public function getStringValue(): ?string
	{
		return (string)$this->getNumericalValue();
	}

	public function getBooleanValue(): ?bool
	{
		return (bool)$this->getNumericalValue();
	}

	public function getArrayValue(): ?array
	{
		return null;
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse([
			"amount" => $this->getAmount()->getRestResponse($request, $options),
			"unit" => (string)$this->getUnit(),
		]);
	}
}
