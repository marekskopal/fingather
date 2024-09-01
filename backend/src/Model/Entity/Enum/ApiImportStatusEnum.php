<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum ApiImportStatusEnum: string
{
	case New = 'New';
	case Waiting = 'Waiting';
	case InProgress = 'InProgress';
	case Finished = 'Finished';
	case Error = 'Error';
}
