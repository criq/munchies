<?php

namespace Fatty;

use Fatty\Exceptions\PregnancyChildbirthDateInPastException;

class Pregnancy
{
	public function setChildbirthDate(?\DateTime $date): Pregnancy
	{
		if (is_null($date)) {
			$this->childbirthDate = null;
		} else {
			if ($date < new \DateTime) {
				throw new PregnancyChildbirthDateInPastException;
			}

			$this->childbirthDate = $date;
		}

		return $this;
	}

	public function setWeightBeforePregnancy(?Weight $weight): Pregnancy
	{
		$this->weightBeforePregnancy = $weight;

		return $this;
	}
}
