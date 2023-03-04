<?php

namespace Fatty;

use Fatty\Metrics\ResultInterface;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Psr\Http\Message\ServerRequestInterface;

class BooleanValue implements ResultInterface
{
	protected $value;

	public function __construct(bool $value = false)
	{
		$this->setValue($value);
	}

	public function __toString(): string
	{
		return $this->getStringValue();
	}

	public function setValue(bool $value): BooleanValue
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): bool
	{
		return $this->value;
	}

	public function getNumericalValue(): ?float
	{
		return $this->getValue() ? 1 : 0;
	}

	public function getStringValue(): ?string
	{
		return (string)$this->getNumericalValue();
	}

	public function getBooleanValue(): ?bool
	{
		return $this->getValue();
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

	public function getFormatted(): ?string
	{
		return $this->getValue() ? "Ano" : "Ne";
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse($this->getValue());
	}
}
