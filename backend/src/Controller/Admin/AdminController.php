<?php

declare(strict_types=1);

namespace FinGather\Controller\Admin;

use FinGather\Service\Request\RequestService;

abstract readonly class AdminController
{
	public function __construct(protected RequestService $requestService)
	{
	}
}
