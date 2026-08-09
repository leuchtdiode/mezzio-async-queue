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
use Ramsey\Uuid\UuidInterface;
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
			// check before every single item, so a shutdown request only has to wait for the running one
			if ($this->shutdownState->isShuttingDown())
			{
				break;
			}

			$this->processItem(
				$item
					->getEntity()
					->getId()
			);
		}
	}

	/**
	 * @throws Throwable
	 */
	private function processItem(UuidInterface $id): void
	{
		// reload, a previously processed item may have cleared the entity manager
		if (!($item = $this->itemProvider->byId($id->toString())))
		{
			return;
		}

		$type = $item->getType();

		// resolve before claiming, a misconfigured type must not leave the item in processing state
		$itemProcessor = $this->getItemProcessor($type);

		// claim atomically, so only one worker picks up the item and only this one is in processing state
		if (!$this->itemProvider->claim($item))
		{
			return;
		}

		try
		{
			$processResult = $itemProcessor->process(
				new ProcessData($item->getPayLoad())
			);
		}
		catch (Throwable $e)
		{
			// log first, marking as failed may throw as well, e.g. when the entity manager got closed
			error_log(sprintf(
				'Async queue item %s of type %s failed: %s - %s',
				$id->toString(),
				$type,
				get_class($e),
				$e->getMessage()
			));

			// do not leave the item in processing state, it would block the shutdown checker forever
			$this->markAsFailed($id);

			return;
		}

		// reload, maybe the source system cleared the entity manager during processing
		if (!($item = $this->itemProvider->byId($id->toString())))
		{
			return;
		}

		$entity = $item->getEntity();

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

	/**
	 * @throws Throwable
	 */
	private function markAsFailed(UuidInterface $id): void
	{
		// reload, the processor may have cleared the entity manager before it threw
		if (!($item = $this->itemProvider->byId($id->toString())))
		{
			return;
		}

		$entity = $item->getEntity();
		$entity->setStatus(Status::FAILED);

		$this->entitySaver->save($entity);
	}

	private function getItemProcessor(string $type): ItemProcessor
	{
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

		return $itemProcessor;
	}
}
