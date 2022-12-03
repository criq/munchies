<?php

namespace Fatty;

use Katu\Tools\Calendar\Timeout;

class ChildCollection extends \ArrayObject
{
	public function filterYoungerThan(Timeout $timeout): ChildCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Child $child) use ($timeout) {
			return $timeout->fits($child->getBirthday()->getTime());
		})));
	}
}
