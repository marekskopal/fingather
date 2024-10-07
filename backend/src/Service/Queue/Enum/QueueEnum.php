<?php

declare(strict_types=1);

namespace FinGather\Service\Queue\Enum;

enum QueueEnum: string
{
	case EmailVerify = 'email-verify';
	case ApiImportPrepareCheck = 'api-import-prepare-check';
	case ApiImportProcessCheck = 'api-import-process-check';
	case UserWarmup = 'user-warmup';
}
