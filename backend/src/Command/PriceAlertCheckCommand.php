<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Dto\PriceAlertNotificationDto;
use FinGather\Model\Repository\PriceAlertRepository;
use FinGather\Service\PriceAlert\PriceAlertChecker;
use FinGather\Service\Queue\Enum\QueueEnum;
use FinGather\Service\Queue\QueuePublisher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PriceAlertCheckCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('priceAlert:check');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$priceAlertChecker = $application->container->get(PriceAlertChecker::class);
		assert($priceAlertChecker instanceof PriceAlertChecker);

		$priceAlertRepository = $application->container->get(PriceAlertRepository::class);
		assert($priceAlertRepository instanceof PriceAlertRepository);

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		$queuePublisher = $application->container->get(QueuePublisher::class);
		assert($queuePublisher instanceof QueuePublisher);

		$triggeredAlerts = $priceAlertChecker->checkAlerts();

		foreach ($triggeredAlerts as $triggeredAlert) {
			$alert = $triggeredAlert['alert'];
			$currentValue = $triggeredAlert['currentValue'];
			$user = $alert->user;

			try {
				$queuePublisher->publishMessage(
					new PriceAlertNotificationDto(
						userId: $user->id,
						priceAlertId: $alert->id,
						currentValue: $currentValue,
					),
					QueueEnum::PriceAlertNotification,
					delay: 1,
				);

				$priceAlertChecker->markTriggered($alert);
				$priceAlertRepository->persist($alert);

				$this->writeln('Queued price alert notification for user ' . $user->id . ' alert ' . $alert->id . '.', $output);
			} catch (\Throwable $e) {
				$logger->error('Error queueing price alert notification for user ' . $user->id . ': ' . $e->getMessage());
				$this->writeln('Error queueing for user ' . $user->id . ': ' . $e->getMessage(), $output);
			}
		}

		$this->writeln('Checked ' . count($triggeredAlerts) . ' triggered alert(s).', $output);

		return self::SUCCESS;
	}
}
