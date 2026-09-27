<?php

declare(strict_types=1);

use FinGather\Tests\Comparator\DecimalComparator;
use SebastianBergmann\Comparator\Factory;

require __DIR__ . '/../vendor/autoload.php';

Factory::getInstance()->register(new DecimalComparator());
