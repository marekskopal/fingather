<?php

declare(strict_types=1);

namespace FinGather\Service\Request;

use FinGather\Dto\ArrayFactoryInterface;
use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Model\Entity\User;
use Nette\Utils\Json;
use Psr\Http\Message\ServerRequestInterface;

final class RequestService implements RequestServiceInterface
{
	public function getUser(ServerRequestInterface $request): User
	{
		$user = $request->getAttribute(AuthorizationMiddleware::AttributeUser);
		assert($user instanceof User);
		return $user;
	}

	/** @return array<mixed> */
	public function getRequestBody(ServerRequestInterface $request): array
	{
		/** @var array<mixed> $decodedBody */
		$decodedBody = Json::decode($request->getBody()->getContents(), forceArrays: true);
		return $decodedBody;
	}

	/**
	 * @param class-string<T> $dtoClass
	 * @return T
	 * @template T of ArrayFactoryInterface
	 */
	public function getRequestBodyDto(ServerRequestInterface $request, string $dtoClass): object
	{
		return $dtoClass::fromArray($this->getRequestBody($request));
	}
}
