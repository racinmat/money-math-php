<?php

namespace MoneyMath;
use Nette\Object;

/**
 * An arbitrary big decimal number with 2-digit fraction part, such as 399.99, 100.00, or
 * 99999999999999999999999999999999999999999999999999999999999999999999999999999999.99
 */
class DecimalWholeNumbers extends Object {

    /**
     * @var string
     */
    private $value;

    /**
     * @param string|int $stringRepresentation
     */
    public function __construct($stringRepresentation) {
        $this->value = gmp_strval(
            gmp_init($stringRepresentation, 10)
        );
    }

    public static function from($number){
        if ($number instanceof DecimalWholeNumbers) {
            return new static($number->__toString());
        } else {
        return new static($number);
        }
    }
//--------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function __toString() {
        return $this->integerValue();
    }

    /**
     * The integer part of this amount (without value part).
     *
     * @return integer Or string, if the number is too big.
     */
    public function integerValue() {
        $sign = (
            gmp_cmp(
                gmp_init($this->value, 10), 0
            ) < 0
        )? '-' : '';

        return $sign . gmp_strval(
            gmp_abs(
                gmp_init($this->value, 10)
            ),
            10
        );
    }

//--------------------------------------------------------------------------------------------------

    /**
     * Creates a new decimal which is a sum of the passed $a and $b.
     *
     * @param DecimalWholeNumbers $a
     * @param DecimalWholeNumbers $b
     * @return DecimalWholeNumbers
     */
    public static function plus(DecimalWholeNumbers $a, DecimalWholeNumbers $b) {
        $ret = new DecimalWholeNumbers('0');

        $ret->value = gmp_strval(
            gmp_add(
                gmp_init($a->value, 10),
                gmp_init($b->value, 10)
            )
        );

        return $ret;
    }

    /**
     * Returns the sum of all the decimal numbers in the array.
     *
     * @param array $decimals The array of DecimalWholeNumbers objects.
     *
     * @return DecimalWholeNumbers or boolean false for an empty $decimals array.
     */
    public static function sum(array $decimals) {
        $ret = new DecimalWholeNumbers('0');

        foreach ($decimals as $d) {
            $ret = DecimalWholeNumbers::plus($ret, $d);
        }

        return $ret;
    }

    /**
     * Returns the average of all the decimal numbers in the array.
     *
     * @param array $decimals The array of DecimalWholeNumbers objects.
     *
     * @return DecimalWholeNumbers or boolean false for an empty $decimals array.
     */
    public static function avg(array $decimals) {
        if (!count($decimals)) return false;

        return DecimalWholeNumbers::div(
            DecimalWholeNumbers::sum($decimals),
            new DecimalWholeNumbers(count($decimals))
        );
    }

    /**
     * Creates a new decimal which is a difference of the passed $a and $b.
     *
     * @param DecimalWholeNumbers $a
     * @param DecimalWholeNumbers $b
     * @return DecimalWholeNumbers
     */
    public static function minus(DecimalWholeNumbers $a, DecimalWholeNumbers $b) {
        $ret = new DecimalWholeNumbers('0');

        $ret->value = gmp_strval(
            gmp_add(
                gmp_init($a->value, 10),
                gmp_neg(gmp_init($b->value, 10))
            )
        );

        return $ret;
    }

    /**
     * Creates a new decimal which is a result of a multiplication of the passed decimal by the
     * passed integer factor.
     *
     * @param DecimalWholeNumbers $decimal
     * @param integer $byIntFactor
     * @return DecimalWholeNumbers
     */
    public static function staticMultiply(DecimalWholeNumbers $decimal, $byIntFactor) {
        $ret = new DecimalWholeNumbers('0');

        $ret->value = gmp_strval(
            gmp_mul(
                gmp_init($decimal->value, 10),
                gmp_init($byIntFactor)
            )
        );

        return $ret;
    }

    /**
     * Creates a new decimal which is a result of a multiplication of the passed decimals.
     *
     * @param DecimalWholeNumbers $a
     * @param DecimalWholeNumbers $b
     * @return DecimalWholeNumbers
     */
    public static function mul(DecimalWholeNumbers $a, DecimalWholeNumbers $b) {
        $ret = new DecimalWholeNumbers('0');

        $ret->value = gmp_strval(
            gmp_mul(
                gmp_init($a->value, 10),
                gmp_init($b->value, 10)
            )
        );

        return $ret;
    }

    /**
     * Creates a new decimal which is a result of a division of $a by $b.
     *
     * @param DecimalWholeNumbers $a
     * @param DecimalWholeNumbers $b
     * @return DecimalWholeNumbers
     */
    public static function div(DecimalWholeNumbers $a, DecimalWholeNumbers $b) {
        $strA = strval($a);
        $strB = strval($b);

        $sign_a = ('-' === $strA[0]) ? -1 : 1;
        $sign_b = ('-' === $strB[0])? -1 : 1;

        $ret = new DecimalWholeNumbers('0');
        $ret->value = gmp_strval(
            gmp_div_q(
                gmp_abs(gmp_init($a->value, 10)),
                gmp_abs(gmp_init($b->value, 10)),
                GMP_ROUND_ZERO
            )
        );

        if (($sign_a * $sign_b) < 0) {
            $ret->value = gmp_strval(
                gmp_neg(
                    gmp_init($ret->value, 10)
                )
            );
        }

        return $ret;
    }

    /**
     * Returns the specified amount of percents of the passed $decimal value.
     *
     * @param DecimalWholeNumbers $decimal
     *
     * @param DecimalWholeNumbers $percents
     *
     * @return DecimalWholeNumbers
     */
    public static function getPercentsOf(DecimalWholeNumbers $decimal, DecimalWholeNumbers $percents) {
        $ret = new DecimalWholeNumbers(strval($decimal));

        $ret->value = gmp_strval(
            gmp_mul(
                gmp_init($ret->value, 10),
                gmp_init($percents->value, 10)
            )
        );

        $ret->value = gmp_strval(
            gmp_div_q(
                gmp_init($ret->value, 10),
                100,
                GMP_ROUND_PLUSINF
            )
        );

        return $ret;
    }

    /**
     * Comparison operator.
     *
     * @param DecimalWholeNumbers $a
     *
     * @param DecimalWholeNumbers $b
     *
     * @return integer Returns a positive value if a > b, zero if a = b and a negative value
     * if a < b
     */

    public static function cmp(DecimalWholeNumbers $a, DecimalWholeNumbers $b) {
        return gmp_cmp(
            gmp_init($a->value, 10),
            gmp_init($b->value, 10)
        );
    }

    public function compare(DecimalWholeNumbers $other) {
        return $this->cmp($this, $other);
    }

    /**
     * @param int|string|float|DecimalWholeNumbers $number
     * @return \MoneyMath\DecimalWholeNumbers
     */
    public function add($number) {
        if ($number instanceof DecimalWholeNumbers) {
            return self::plus($this, $number);
        } else {
            return $this->add(DecimalWholeNumbers::from($number));
        }

    }

    /**
     * @param int|string|float|DecimalWholeNumbers $number
     * @return \MoneyMath\DecimalWholeNumbers
     */
    public function subtract($number){
        if ($number instanceof DecimalWholeNumbers) {
            return self::minus($this, $number);
        } else {
            return $this->subtract(DecimalWholeNumbers::from($number));
        }
    }

    /**
     * @param int|string|float|DecimalWholeNumbers $number
     * @return \MoneyMath\DecimalWholeNumbers
     */
    public function multiply($number){
        if ($number instanceof DecimalWholeNumbers) {
            return self::mul($this, $number);
        } else {
            return $this->multiply(DecimalWholeNumbers::from($number));
        }
    }

    /**
     * @param int|string|float|DecimalWholeNumbers $number
     * @return \MoneyMath\DecimalWholeNumbers
     */
    public function divide($number){
        if ($number instanceof DecimalWholeNumbers) {
            return self::div($this, $number);
        } else {
            return $this->divide(DecimalWholeNumbers::from($number));
        }
    }

    /**
     * @param int $number
     * @return \MoneyMath\DecimalWholeNumbers
     */
    public function multiplyBy($integer){
        return self::staticMultiply($this, $integer);
    }

    public function isZero() {
        $zero = $this->from(0);
        return $this->compare($zero) === 0;
    }

    /** @return bool */
    public function isPositive() {
        $zero = $this->from(0);
        return $this->compare($zero) > 0;
    }

    /** @return bool */
    public function isNegative() {
        $zero = $this->from(0);
        return $this->compare($zero) < 0;
    }

    /**
     * Works only when no value are present.
     * @param $number
     * @return \MoneyMath\DecimalWholeNumbers
     */
    public function modulo($number){
        $iter = $this->from($this->value);
        while($iter->divide($number)->isPositive()) {
            $iter = $iter->divide($number);
        }
        $remainer = $iter->integerValue();
        return static::from($remainer);
    }

}
