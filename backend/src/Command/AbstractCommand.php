<?php

declare(strict_types=1);

namespace FinGather\Command;

use Safe\DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
	protected function writeln(string $message, OutputInterface $output): void
	{
		$datetime = new DateTimeImmutable();

		$output->writeln($datetime->format('Y-m-d h:i:s') . ' ' . $message);
	}
}
