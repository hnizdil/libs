<?php

namespace Hnizdil;

class Money
{

	const SCALE = 2;

	private $amount;

	private $locale;

	public function __construct($amount) {

		$this->amount = $amount;

	}

	public function add(self $another = NULL) {

		if ($another === NULL || $another->isEmpty()) {
			return new self($this->amount);
		}

		return new self(
			bcadd(
				$this->format($this->amount),
				$this->format($another->amount),
				self::SCALE));

	}

	public function sub(self $another) {

		if ($another === NULL || $another->isEmpty()) {
			return new self($this->amount);
		}

		return new self(
			bcsub(
				$this->format($this->amount),
				$this->format($another->amount),
				self::SCALE));

	}

	public function mul($factor) {

		return new self(
			bcmul(
				$this->format($this->amount),
				$this->format($factor),
				self::SCALE));

	}

	public function div($divisor) {

		return new self(
			bcdiv(
				$this->format($this->amount),
				$this->format($divisor),
				self::SCALE));

	}

	public function equals(self $money) {

		$result = bccomp(
			$this->format($this->amount),
			$money->getAmount(),
			self::SCALE);

		return $result === 0;

	}

	public function isEmpty() {

		return $this->amount === '';

	}

	public function isZero() {

		return $this->equals(new self('0'));

	}

	public function getAmount() {

		return $this->amount;

	}

	public function setLocale($locale) {

		$this->locale = $locale;

		return $this;

	}

	private function format($amount) {

		if (!$amount) {
			$amount = 0;
		}

		return number_format($amount, self::SCALE, '.', '');

	}

	public function __toString() {

		if (!is_numeric($this->amount)) {
			return '';
		}

		// detekce desetinné části
		$multiplier = pow(10, self::SCALE);
		$hasDecimalPart =
			intval($this->amount) * $multiplier !==
			intval($this->amount  * $multiplier);

		// zobrazit desetiny pouze pokud nějaké má
		$format = $hasDecimalPart ? '%n' : '%.0n';

		// nastavení požadovaného locale
		if ($this->locale) {
			$originalLocale = setlocale(LC_MONETARY, 0);
			setlocale(LC_MONETARY, $this->locale);
		}

		// naformátování částky podle locale
		$str = money_format($format, $this->amount);

		// navrácení původního locale
		if ($this->locale) {
			setlocale(LC_MONETARY, $originalLocale);
		}

		// vložení nezlomitelných mezer do výsledku
		return str_replace(' ', "\xc2\xa0", $str);

	}

}
