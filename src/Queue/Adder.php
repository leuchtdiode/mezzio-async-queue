<?php
namespace AsyncQueue\Queue;

use AsyncQueue\Item\Entity;
use AsyncQueue\Item\EntitySaver;
use AsyncQueue\Item\Provider;
use AsyncQueue\Item\Filter as DbFilter;
use AsyncQueue\Item\Status;
use Common\Db\FilterChain;
use DateTime;
use Throwable;

class Adder
{
	public function __construct(
		private readonly EntitySaver $entitySaver,
		private readonly Provider $provider
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function add(AddData $data): void
	{
		if ($data->isAddOnlyIfNotInQueue() && $this->alreadyInQueue($data))
		{
			return;
		}

		$entity = new Entity();
		$entity->setType($data->getType());
		$entity->setPayLoad($data->getPayLoad());
		$entity->setProcessAfter(
			$data->getProcessAfter() ?? new DateTime()
		);

		$this->entitySaver->save($entity);
	}

	/**
	 * @throws Throwable
	 */
	private function alreadyInQueue(AddData $data): bool
	{
		return $this->provider->countWithFilter(
			FilterChain::create()
				->addFilter(DbFilter\Type::is($data->getType()))
				->addFilter(DbFilter\Status::is(Status::PENDING))
				->addFilter(DbFilter\PayLoad::is(json_encode($data->getPayLoad())))
		);
	}
}

