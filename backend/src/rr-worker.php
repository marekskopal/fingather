<?php

declare(strict_types=1);

namespace FinGather;

use FinGather\App\Application;
use FinGather\App\RoadRunnerProcessor;

require_once __DIR__ . '/../vendor/autoload.php';

new Application(new RoadRunnerProcessor());
