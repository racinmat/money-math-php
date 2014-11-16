<?php
/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 16. 11. 2014
 * Time: 16:48
 * Copyright © 2014, Matěj Račinský. Všechna práva vyhrazena.
 */

namespace MoneyMath;

use Money\InvalidArgumentException;
use Nette\Object;

/**
 * Class Money
 * @package MoneyMath
 * @author: Matěj Račinský 
 */
class Money extends Object {
    const ROUND_HALF_UP = PHP_ROUND_HALF_UP;
    const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;
    const ROUND_HALF_EVEN = PHP_ROUND_HALF_EVEN;
    const ROUND_HALF_ODD = PHP_ROUND_HALF_ODD;

    /**
     * @var \MoneyMath\Decimal2
     */
    private $amount;

    /** @var \Money\Currency */
    private $currency;

    /**
     * Create a Money instance
     * @param  integer|string $amount
     * @param  \Money\Currency $currency
     * @throws \Money\InvalidArgumentException
     */
    public function __construct($amount, Currency $currency) {
        $this->amount = new Decimal2($amount);
        $this->currency = $currency;
    }

    /**
     * @param \MoneyMath\Money $other
     * @return bool
     */
    public function isSameCurrency(Money $other) {
        return $this->currency->equals($other->currency);
    }

    /**
     * @param \MoneyMath\Money $other
     * @throws InvalidArgumentException
     */
    private function assertSameCurrency(Money $other) {
        if (!$this->isSameCurrency($other)) {
            throw new InvalidArgumentException('Different currencies');
        }
    }

    /**
     * @param \MoneyMath\Money $other
     * @return bool
     */
    public function equals(Money $other) {
        return
            $this->isSameCurrency($other)
            && $this->amount == $other->amount;
    }

    /**
     * @param \MoneyMath\Money $other
     * @return int
     */
    public function compare(Money $other) {
        $this->assertSameCurrency($other);
        $result = $this->amount->compare($other->amount);
        if ($result < 0) {
            return -1;
        } elseif ($result) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * @param \MoneyMath\Money $other
     * @return bool
     */
    public function greaterThan(Money $other) {
        return 1 == $this->compare($other);
    }

    /**
     * @param \MoneyMath\Money $other
     * @return bool
     */
    public function lessThan(Money $other) {
        return -1 == $this->compare($other);
    }

    /**
     * Return only integer value, without cents.
     * @return int
     */
    public function getAmount() {
        return $this->amount->integerValue();
    }

    /**
     * @return \Money\Currency
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param \MoneyMath\Money $addend
     *@return \MoneyMath\Money
     */
    public function add(Money $addend) {
        $this->assertSameCurrency($addend);
        return new self($this->amount->add($addend->amount), $this->currency);
    }

    /**
     * @param \MoneyMath\Money $subtrahend
     * @return \MoneyMath\Money
     */
    public function subtract(Money $subtrahend) {
        $this->assertSameCurrency($subtrahend);
        return new self($this->amount->subtract($subtrahend->amount), $this->currency);
    }

    /**
     * @throws \MoneyMath\InvalidArgumentException
     */
    private function assertOperand($operand) {
        if (!is_int($operand)) {
            throw new InvalidArgumentException('Operand should be an integer');
        }
    }

    /**
     * @param $multiplier
     * @param int $rounding_mode
     * @return \MoneyMath\Money
     */
    public function multiply($multiplier) {
        $this->assertOperand($multiplier);
        $product = $this->amount->multiplyBy($multiplier);
        return new Money($product, $this->currency);
    }

    /**
     * @param $divisor
     * @return \Money\Money
     */
    public function divide($divisor) {
        $this->assertOperand($divisor);
        if ($divisor instanceof Decimal2) {
            $quotient = $this->amount->divide($divisor);
        } else {
            $quotient = $this->amount->divide(Decimal2::from($divisor));
        }
        return new Money($quotient, $this->currency);
    }

    /**
     * Allocate the money according to a list of ratio's
     * @param array $ratios List of ratio's
     * @return \Money\Money
     */
    public function allocate(array $ratios) {
        $remainder = $this->amount;
        $results = array();
        $total = array_sum($ratios);

        foreach ($ratios as $ratio) {
            $share = $this->amount->multiplyBy($ratio)->divide(Decimal2::from($total));
            $results[] = new Money($share, $this->currency);
            $remainder = $remainder->subtract($share);
        }
        for ($i = 0; $remainder > 0; $i++) {
            $results[$i] = $results[$i]->amount->add(1);
            $remainder = $remainder->subtract(1);
        }

        return $results;
    }

    /** @return bool */
    public function isZero() {
        $zero = $this->amount->from(0);
        return $this->amount->compare($zero) === 0;
    }

    /** @return bool */
    public function isPositive() {
        $zero = $this->amount->from(0);
        return $this->amount->compare($zero) > 0;
    }

    /** @return bool */
    public function isNegative() {
        $zero = $this->amount->from(0);
        return $this->amount->compare($zero) < 0;
    }

    /**
     * @param $string
     * @throws \Money\InvalidArgumentException
     * @return int
     */
    public static function stringToUnits( $string ) {
        $sign = "(?P<sign>[-\+])?";
        $digits = "(?P<digits>\d*)";
        $separator = "(?P<separator>[.,])?";
        $decimals = "(?P<decimal1>\d)?(?P<decimal2>\d)?";
        $pattern = "/^".$sign.$digits.$separator.$decimals."$/";

        if (!preg_match($pattern, trim($string), $matches)) {
            throw new InvalidArgumentException("The value could not be parsed as money");
        }

        $units = $matches['sign'] == "-" ? "-" : "";
        $units .= $matches['digits'];
        $units .= isset($matches['decimal1']) ? $matches['decimal1'] : "0";
        $units .= isset($matches['decimal2']) ? $matches['decimal2'] : "0";

        return (int) $units;
    }

}