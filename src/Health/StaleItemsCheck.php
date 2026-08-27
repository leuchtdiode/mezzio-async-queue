<?php
declare(strict_types=1);

namespace AsyncQueue\Health;

use AsyncQueue\Item\Filter\ProcessAfter as ProcessAfterFilter;
use AsyncQueue\Item\Filter\Status as StatusFilter;
use AsyncQueue\Item\Repository;
use AsyncQueue\Item\Status;
use Common\Db\FilterChain;
use DateTime;
use Monitoring\Health\Check;
use Monitoring\Health\CheckResult;
use RuntimeException;
use Throwable;

readonly class StaleItemsCheck implements Check
{
	private const int DEFAULT_THRESHOLD_MINUTES = 60;

	public function __construct(
		private array $config,
		private Repository $repository
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function check(): CheckResult
	{
		if (!interface_exists('\Monitoring\Health\Check'))
		{
			throw new RuntimeException('leuchtdiode/mezzio-monitoring is mandatory');
		}

		$result = new CheckResult();
		$result->setKey('async-queue-stale-items');

		$thresholdMinutes = $this->getThresholdMinutes();

		// processAfter is the due date the processor itself selects by, so items scheduled for later
		// or backed off after a retry are correctly not stale yet
		$dueBefore = new DateTime();
		$dueBefore->modify('-' . $thresholdMinutes . ' minute');

		// failed and success are terminal, those items are never picked up again
		$pendingCount    = $this->countStale(Status::PENDING, $dueBefore);
		$processingCount = $this->countStale(Status::PROCESSING, $dueBefore);

		$result->setHealthy($pendingCount === 0 && $processingCount === 0);

		if ($pendingCount)
		{
			$result->addMessage(sprintf(
				'%d item(s) are pending for longer than %d minute(s), please check',
				$pendingCount,
				$thresholdMinutes
			));
		}

		// an item stuck in processing means the worker died after claiming it, nothing moves it back
		if ($processingCount)
		{
			$result->addMessage(sprintf(
				'%d item(s) are processing for longer than %d minute(s), please check',
				$processingCount,
				$thresholdMinutes
			));
		}

		return $result;
	}

	/**
	 * @throws Throwable
	 */
	private function countStale(string $status, DateTime $dueBefore): int
	{
		return $this->repository->countWithFilter(
			FilterChain::create()
				->addFilter(StatusFilter::is($status))
				->addFilter(ProcessAfterFilter::before($dueBefore))
		);
	}

	private function getThresholdMinutes(): int
	{
		return (int)($this->config['async-queue']['monitoring']['staleItems']['thresholdMinutes']
			?? self::DEFAULT_THRESHOLD_MINUTES);
	}
}
