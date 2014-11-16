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
$money = new \MoneyMath\Money(40, new \MoneyMath\Currency("czk"));
var_dump($money->divide(7));
var_dump(substr("ahoj",0,1));