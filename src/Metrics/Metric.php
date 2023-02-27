<?php

namespace Fatty\Metrics;

use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Tools\Strings\Code;
use Katu\Types\TClass;
use Psr\Http\Message\ServerRequestInterface;

abstract class Metric implements RestResponseInterface
{
	public function getCode(): string
	{
		$string = (new Code((new TClass(static::class))->getShortName()))->getConstantFormat();
		$string = ltrim($string, "_");
		$string = preg_replace("/_METRIC$/", "", $string);

		return $string;
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse([
			"code" => $this->getCode(),
		]);
	}
}
