<?php
/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 16. 11. 2014
 * Time: 17:37
 * Copyright © 2014, Matěj Račinský. Všechna práva vyhrazena.
 */
require 'vendor/autoload.php';
//require 'src.Mon';
//$money = new \MoneyMath\Money(40, new \MoneyMath\Currency("czk"));
//var_dump($money->divide(7));
//var_dump(substr("ahoj",0,1));
$money4 = new \MoneyMath\DecimalWholeNumbers(50);
var_dump($money4);
$money3 = \MoneyMath\DecimalWholeNumbers::from(-50);
var_dump($money3);
$money = new \MoneyMath\DecimalWholeNumbers("5000000000000000");
var_dump($money);
$money2 = new \MoneyMath\DecimalWholeNumbers("-5000000000000000000000000");
$money5=$money->add($money2);
var_dump($money2);
echo("add");
var_dump($money5);
echo("integer value");
var_dump($money5->integerValue());
echo("divide");
var_dump($money2->divide($money));
echo("multiply");
var_dump($money2->multiply($money));
echo("subtract");
var_dump($money2->subtract($money));
$decimal2 = new \MoneyMath\Decimal2(500);
$decimal21 = new \MoneyMath\Decimal2(20);
var_dump($decimal21->getPercentsOf($decimal2, $decimal2)->integerValue());
$decimal2 = new \MoneyMath\DecimalWholeNumbers(500);
$decimal21 = new \MoneyMath\DecimalWholeNumbers(20);
var_dump($decimal21->getPercentsOf($decimal2, $decimal2)->integerValue());
