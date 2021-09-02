<?php

namespace Fatty\Exceptions;

class FattyExceptionCollection extends FattyException implements \Countable, \ArrayAccess, \Iterator
{
	protected $exceptions = [];
	protected $offset = 0;

	public function add(FattyException $e) : FattyExceptionCollection
	{
		if (is_iterable($e)) {
			foreach ($e as $_e) {
				$this->add($_e);
			}
		} else {
			$this->exceptions[] = $e;
		}

		return $this;
	}

	public function getExceptions() : array
	{
		return $this->exceptions;
	}

	/****************************************************************************
	 * Interfaces.
	 */

	public function count() : int
	{
		return count($this->exceptions);
	}

	public function offsetExists($offset) : bool
	{
		return isset($this->exceptions[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->exceptions[$offset];
	}

	public function offsetSet($offset, $value) : void
	{
		if (is_null($offset)) {
			$this->exceptions[] = $value;
		} else {
			$this->exceptions[$offset] = $value;
		}
	}

	public function offsetUnset($offset) : void
	{
		unset($this->exceptions[$offset]);
	}

	public function current()
	{
		return $this->exceptions[$this->offset];
	}

	public function next()
	{
		++$this->offset;
	}

	public function key() : int
	{
		return $this->offset;
	}

	public function valid() : bool
	{
		return isset($this->exceptions[$this->offset]);
	}

	public function rewind() : void
	{
		$this->offset = 0;
	}
}
