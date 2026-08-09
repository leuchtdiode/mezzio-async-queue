<?php
namespace AsyncQueue\Item;

use Common\Db\FilterChain;
use Common\Db\OrderChain;
use Throwable;

class Provider
{
	public function __construct(
		private readonly Repository $repository,
		private readonly Creator $creator
	)
	{
	}

	public function byId(string $id): ?Item
	{
		return ($entity = $this->repository->find($id))
			? $this->createDto($entity)
			: null;
	}

	/**
	 * Atomically claims the item for the current worker by moving it to processing.
	 * Returns false if another worker claimed it first.
	 */
	public function claim(Item $item): bool
	{
		$entity = $item->getEntity();

		if (!$this->repository->claim($entity->getId()))
		{
			return false;
		}

		// the update bypasses the entity manager, so keep the loaded entity in sync
		$entity->setStatus(Status::PROCESSING);

		return true;
	}

	/**
	 * @return Item[]
	 */
	public function filter(FilterChain $filterChain, ?OrderChain $orderChain = null, ?int $limit = null): array
	{
		return $this->createDtos(
			$this->repository->filter(
				filterChain: $filterChain,
				orderChain: $orderChain,
				limit: $limit ?? Repository::DEFAULT_LIMIT
			)
		);
	}

	/**
	 * @throws Throwable
	 */
	public function countWithFilter(FilterChain $filterChain): int
	{
		return $this->repository->countWithFilter($filterChain);
	}

	/**
	 * @param Entity[] $entities
	 * @return Item[]
	 */
	private function createDtos(array $entities): array
	{
		return array_map(
			function (Entity $entity)
			{
				return $this->createDto($entity);
			},
			$entities
		);
	}

	private function createDto(Entity $entity): Item
	{
		return $this->creator->byEntity($entity);
	}
}
