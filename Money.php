<?php

namespace Hnizdil;

class Money
{

	const SCALE = 2;

	private $amount;
	private $currency;

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

	public function setCurrency($currency) {

		$this->currency = $currency;

		return $this;

	}

	public function __toString() {

		if (!is_numeric($this->amount)) {
			return '';
		}

		if ($this->currency) {
			$str = number_format($this->amount, 0, '', ' ');
			switch ($this->currency) {
			case 'CZK':
				$str .= ' Kč';
				break;
			case 'EUR':
				$str .= ' €';
				break;
			}
		}
		else {
			$str = money_format('%n', $this->amount);
		}

		return str_replace(' ', "\xc2\xa0", $str);

	}

	private function format($amount) {

		if (!$amount) {
			$amount = 0;
		}

		return number_format($amount, self::SCALE, '.', '');

	}

}
