<?php

namespace Fatty;

use Fatty\Exceptions\PregnancyChildbirthDateInPastException;
use Katu\Tools\Calendar\Time;

class Pregnancy
{
	public function setChildbirthDate(?Time $date): Pregnancy
	{
		if (is_null($date)) {
			$this->childbirthDate = null;
		} else {
			if ($date < new Time()) {
				throw new PregnancyChildbirthDateInPastException;
			}

			$this->childbirthDate = $date;
		}

		return $this;
	}

	public function getChildbirthDate(): ?Time
	{
		return $this->childbirthDate;
	}

	public function setWeightBeforePregnancy(?Weight $weight): Pregnancy
	{
		$this->weightBeforePregnancy = $weight;

		return $this;
	}

	public function getIsPregnant(): bool
	{
		try {
			return $this->getChildbirthDate() > new Time;
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}
}
