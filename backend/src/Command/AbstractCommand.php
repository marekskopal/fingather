<?php

declare(strict_types=1);

namespace FinGather\Command;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
	private UuidInterface $id;

	public function __construct(?string $name = null)
	{
		parent::__construct($name);

		$this->id = Uuid::uuid4();
	}

	abstract protected function process(InputInterface $input, OutputInterface $output): int;

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->writeln('Command started.', $output);

		$result = $this->process($input, $output);

		$this->writeln('Command ended.', $output);

		return $result;
	}

	protected function writeln(string $message, OutputInterface $output): void
	{
		$datetime = new DateTimeImmutable();

		$output->writeln(sprintf(
			'%s [%s] %s: %s',
			$datetime->format('Y-m-d H:i:s'),
			$this->id->toString(),
			$this->getName(),
			$message,
		));
	}
}
