<?php

namespace Fatty\Metrics;

use Katu\Tools\Rest\RestResponseInterface;

interface ResultInterface extends RestResponseInterface
{
	public function getArrayValue(): ?array;
	public function getBooleanValue(): ?bool;
	public function getFormatted(): ?string;
	public function getInUnit(string $unit): ?ResultInterface;
	public function getNumericalValue(): ?float;
	public function getStringValue(): ?string;
	public function getUnit(): ?string;
}
