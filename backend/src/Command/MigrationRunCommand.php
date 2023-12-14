<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\Service\Dbal\DbContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationRunCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('migration:run');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$host = (string) getenv('MYSQL_HOST');
		$database = (string) getenv('MYSQL_DATABASE');
		/** @var non-empty-string $user */
		$user = (string) getenv('MYSQL_USER');
		/** @var non-empty-string $password */
		$password = (string) getenv('MYSQL_PASSWORD');

		$dbContext = new DbContext(dsn: 'mysql:host=' . $host . ';dbname=' . $database, user: $user, password: $password);

		$migrator = $dbContext->getMigrator();

		while ($migrator->run() !== null) { //phpcs:ignore
		}

		return 0;
	}
}
