<?php

declare(strict_types=1);

namespace FinGather\Route;

use League\Route\Router;

class RouteList
{
	/**
	 * @var array{
	 *     GET?:array<string, callable|array<string>>,
	 *     POST?:array<string, callable|array<string>>,
	 *     PUT?:array<string, callable|array<string>>,
	 *     DELETE?:array<string, callable|array<string>>,
	 *     PATCH?:array<string, callable|array<string>>,
	 *     HEAD?:array<string, callable|array<string>>,
	 * }
	 */
	private array $routeList;

	/**
	 * @return array{
	 *     GET?:array<string, callable|array<string>>,
	 *     POST?:array<string, callable|array<string>>,
	 *     PUT?:array<string, callable|array<string>>,
	 *     DELETE?:array<string, callable|array<string>>,
	 *     PATCH?:array<string, callable|array<string>>,
	 *     HEAD?:array<string, callable|array<string>>,
	 * }
	 */
	public function getRouteList(): array
	{
		return $this->routeList;
	}

	/** @param callable|array<string> $handler */
	public function get(string $route, callable|array $handler): self
	{
		$this->routeList['GET'][$route] = $handler;

		return $this;
	}

	/** @param callable|array<string> $handler */
	public function post(string $route, callable|array $handler): self
	{
		$this->routeList['POST'][$route] = $handler;

		return $this;
	}

	/** @param callable|array<string> $handler */
	public function put(string $route, callable|array $handler): self
	{
		$this->routeList['PUT'][$route] = $handler;

		return $this;
	}

	/** @param callable|array<string> $handler */
	public function delete(string $route, callable|array $handler): self
	{
		$this->routeList['DELETE'][$route] = $handler;

		return $this;
	}

	/** @param callable|array<string> $handler */
	public function patch(string $route, callable|array $handler): self
	{
		$this->routeList['PATCH'][$route] = $handler;

		return $this;
	}

	/** @param callable|array<string> $handler */
	public function head(string $route, callable|array $handler): self
	{
		$this->routeList['HEAD'][$route] = $handler;

		return $this;
	}

	public function setRouteListToRouter(Router $router): void
	{
		foreach ($this->routeList ?? [] as $method => $routeHandlers) {
			foreach ($routeHandlers as $route => $handler) {
				$router->map($method, $route, $handler);
			}
		}
	}
}
