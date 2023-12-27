<?php

declare(strict_types=1);

namespace FinGather;

use FinGather\App\RoadRunnerWorker;

require_once __DIR__ . '/../vendor/autoload.php';

(new RoadRunnerWorker())->run();
