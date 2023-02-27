<?php

namespace Fatty;

use Fatty\Exceptions\InvalidAmountException;
use Fatty\Metrics\ResultInterface;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Psr\Http\Message\ServerRequestInterface;

class Amount implements ResultInterface
{
	protected $value;

	public function __construct(float $value = 0)
	{
		$this->value = $value;
	}

	public function __toString(): string
	{
		return $this->getFormatted();
	}

	public static function createFromString(string $value): ?Amount
	{
		try {
			$value = trim($value);
			if (!preg_match("/^\-?[0-9]+([\,\.][0-9]+)?$/", $value)) {
				throw new InvalidAmountException;
			}

			return new static((new \Katu\Types\TString(trim($value)))->getAsFloat());
		} catch (\Throwable $e) {
			return null;
		}

		return null;
	}

	public function getValue(): ?float
	{
		return (float)$this->value;
	}

	public function getMultiplied(float $value): Amount
	{
		return new static($this->getValue() * $value);
	}

	public function getFormatted(?Locale $locale = null): string
	{
		$locale = $locale ?: Locale::getDefault();

		$numberFormatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
		$numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 1);

		return $numberFormatter->format($this->getValue());
	}

	public function getNumericalValue(): ?float
	{
		return (float)$this->getValue();
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

	public function getInUnit(string $unit): ?ResultInterface
	{
		return $this;
	}

	public function getUnit(): ?string
	{
		return null;
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse((float)$this->getValue());
	}
}
