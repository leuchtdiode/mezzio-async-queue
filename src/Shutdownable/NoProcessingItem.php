<?php
declare(strict_types=1);

namespace AsyncQueue\Shutdownable;

use AsyncQueue\Item\Filter as ItemDbFilter;
use AsyncQueue\Item\Repository;
use AsyncQueue\Item\Status;
use Common\Cli\Shutdownable;
use Common\Db\FilterChain;
use Throwable;

readonly class NoProcessingItem implements Shutdownable
{
	public function __construct(
		private Repository $repository,
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function isShutdownable(): bool
	{
		$processingCount = $this->repository->countWithFilter(
			FilterChain::create()
				->addFilter(ItemDbFilter\Status::is(Status::PROCESSING))
		);

		return $processingCount === 0;
	}
}
