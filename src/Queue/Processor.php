<?php
namespace AsyncQueue\Queue;

use AsyncQueue\Item\EntitySaver;
use AsyncQueue\Item\Filter\ProcessAfter as ProcessAfterFilter;
use AsyncQueue\Item\Filter\Status as StatusFilter;
use AsyncQueue\Item\Filter\Type as TypeFilter;
use AsyncQueue\Item\Order\ProcessAfter as ProcessAfterOrder;
use AsyncQueue\Item\ProcessData;
use AsyncQueue\Item\Processor as ItemProcessor;
use AsyncQueue\Item\Provider;
use AsyncQueue\Item\Status;
use Common\Db\FilterChain;
use Common\Db\OrderChain;
use Common\Shutdown\State as ShutdownState;
use DateTime;
use Exception;
use Psr\Container\ContainerInterface;
use Throwable;

class Processor
{
	public function __construct(
		private readonly array $config,
		private readonly ContainerInterface $container,
		private readonly Provider $itemProvider,
		private readonly EntitySaver $entitySaver,
		private readonly ShutdownState $shutdownState
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function process(ProcessParams $params): void
	{
		if ($this->shutdownState->isShuttingDown())
		{
			return;
		}

		$now = new DateTime();

		$filterChain = FilterChain::create()
			->addFilter(StatusFilter::is(Status::PENDING))
			->addFilter(ProcessAfterFilter::before($now));

		if (($types = $params->getTypes()))
		{
			$filterChain->addFilter(
				TypeFilter::in($types)
			);
		}

		if (($excludeTypes = $params->getExcludeTypes()))
		{
			$filterChain->addFilter(
				TypeFilter::notIn($excludeTypes)
			);
		}

		$items = $this->itemProvider->filter(
			$filterChain,
			OrderChain::create()
				->addOrder(ProcessAfterOrder::asc()),
			$params->getLimit()
		);

		foreach ($items as $item)
		{
			$entity = $item->getEntity();
			$entity->setStatus(Status::PROCESSING);

			$this->entitySaver->save($entity);
		}

		foreach ($items as $item)
		{
			$entity = $item->getEntity();

			$type = $item->getType();

			$itemProcessorClass = $this->config['async-queue']['processors'][$type] ?? null;

			if (!$itemProcessorClass || !$this->container->has($itemProcessorClass))
			{
				throw new Exception('Could not find processor class type ' . $type . '. Did you specify in config?');
			}

			$itemProcessor = $this->container->get($itemProcessorClass);

			if (!$itemProcessor instanceof ItemProcessor)
			{
				throw new Exception('Specified item processor ' . $itemProcessorClass . ' does not implement Processor interface');
			}

			$processResult = $itemProcessor->process(
				new ProcessData($item->getPayLoad())
			);

			// reload, maybe the source system cleared the entity manager during processing
			$entity = $this->itemProvider
				->byId($entity->getId())
				->getEntity();

			if ($processResult->isChangePayload())
			{
				$entity->setPayLoad($processResult->getNewPayLoad());
			}

			if (($success = $processResult->isSuccess()) !== null)
			{
				$entity->setStatus(
					$success
						? Status::SUCCESS
						: Status::FAILED
				);
			}
			else
			{
				if (($retryInSeconds = $processResult->getRetryInSeconds()))
				{
					$processAfter = new DateTime();
					$processAfter->modify('+ ' . $retryInSeconds . ' seconds');

					$entity->setProcessAfter($processAfter);
					$entity->setStatus(Status::PENDING);
				}
			}

			$this->entitySaver->save($entity);
		}
	}
}
