<?php

namespace Hnizdil\Service;

use DateTime;
use Nette\Caching\Cache;
use Hnizdil\Service\BusinessDate\BusinessDateException as e;

class BusinessDate
{

	const CACHE_KEY = 'holidays';

	private $cache;

	public function __construct(Cache $cache) {

		$this->cache = $cache;

	}

	public function modify(DateTime $date, $modify) {

		$dest = clone $date;
		$dest->modify($modify);

		$op = $dest < $date ? '-' : '+';

		if ($dest != $date) {
			do {

				$date->modify("{$op}1 day");

				if ($this->isBusinessDay($date)) {
					$dest->modify("{$op}1 day");
				}

			}
			while ($date != $dest);
		}

		return $date;

	}

	public function isBusinessDay(DateTime $date) {

		static $holidays = NULL;

		if ($holidays === NULL) {
			$holidays = $this->getHolidays();
		}

		// holiday
		if (isset($holidays[$date->format('Ymd')])) {
			return TRUE;
		}
		// weekend
		elseif ($date->format('N') > 5) {
			return TRUE;
		}
		else {
			return FALSE;
		}

	}

	private function getHolidays() {

		$holidays = $this->cache->load(self::CACHE_KEY, function() {
			return array();
		});

		if ($holidays) {
			return $holidays;
		}

		$path = realpath(__DIR__) . '/holidays.csv';

		if (!is_readable($path)) {
			e::notFoundOrNotReadable($path);
		}

		$resource = fopen($path, 'r');

		while ($row = fgetcsv($resource)) {
			$holidays[vsprintf('%04d%02d%02d', $row)] = 1;
		}

		fclose($resource);

		$this->cache->save(self::CACHE_KEY, $holidays);

		return $holidays;

	}

}
