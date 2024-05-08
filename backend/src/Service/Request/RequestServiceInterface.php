<?php

declare(strict_types=1);

namespace FinGather\Service\Request;

use FinGather\Model\Entity\User;
use Psr\Http\Message\ServerRequestInterface;

interface RequestServiceInterface
{
	public function getUser(ServerRequestInterface $request): User;
}
