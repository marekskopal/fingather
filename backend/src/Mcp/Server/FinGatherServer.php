<?php

declare(strict_types=1);

namespace FinGather\Mcp\Server;

use Mcp\Server;
use Mcp\Server\Session\SessionStoreInterface;
use Psr\Container\ContainerInterface;

/** @api */
final readonly class FinGatherServer
{
	public function __construct(private ContainerInterface $container)
	{
	}

	public function build(?SessionStoreInterface $sessionStore = null): Server
	{
		$builder = Server::builder()
			->setContainer($this->container)
			->setDiscovery(
				basePath: dirname(__DIR__, 2),
				scanDirs: ['Mcp/Tool'],
			)
			->setServerInfo(name: 'fingather', version: '1.0.0', description: 'FinGather portfolio tracking MCP server')
			->setInstructions(
				'This server provides access to a FinGather investment portfolio. ' .
				'Use list_portfolios first to discover available portfolios and their IDs. ' .
				'Monetary values are returned as decimal strings in the portfolio\'s default currency. ' .
				'Use search_tickers to look up ticker symbols before adding transactions.',
			);

		if ($sessionStore !== null) {
			$builder->setSession($sessionStore);
		}

		return $builder->build();
	}
}
