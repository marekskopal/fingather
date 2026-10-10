<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\Service\Dbal\DbContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Writes the ORM schema, with its generated hydrators and extractors, to DbContext::SchemaFile so
 * production processes load it under opcache instead of scanning entity attributes. Runs in the
 * image build; needs no database.
 */
final class OrmSchemaDumpCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('orm:schema-dump');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		DbContext::createSchemaBuilder()->dump(DbContext::SchemaFile);

		$this->writeln('ORM schema written to ' . DbContext::SchemaFile, $output);

		return self::SUCCESS;
	}
}
