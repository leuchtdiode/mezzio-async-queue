<?php
declare(strict_types=1);

namespace AsyncQueue\Queue;

use Common\Shutdown\State as ShutdownState;
use Doctrine\ORM\EntityManager;
use Throwable;

readonly class Worker
{
	public function __construct(
		private Processor $processor,
		private ShutdownState $shutdownState,
		private EntityManager $entityManager
	)
	{
	}

	/**
	 * Keeps processing until a shutdown is requested or the max runtime is exceeded,
	 * so pending items are picked up within the sleep interval instead of waiting for the next cron run.
	 *
	 * @throws Throwable
	 */
	public function run(WorkerParams $params): void
	{
		$sleepMicroSeconds = max(0, (int)round($params->getSleepSeconds() * 1000000));

		$endTime = ($maxRuntimeSeconds = $params->getMaxRuntimeSeconds())
			? time() + $maxRuntimeSeconds
			: null;

		while (!$this->shutdownState->isShuttingDown())
		{
			$processedCount = $this->processor->process(
				$params->getProcessParams()
			);

			// a closed entity manager can not be recovered, let the process supervisor start a fresh worker
			if (!$this->entityManager->isOpen())
			{
				return;
			}

			// nothing references the processed items anymore, so keep the identity map from growing over time
			$this->entityManager->clear();

			if ($endTime !== null && time() >= $endTime)
			{
				return;
			}

			// only wait when there was nothing to do, so bursts are drained without sleeping in between
			if ($processedCount === 0)
			{
				usleep($sleepMicroSeconds);
			}
		}
	}
}
