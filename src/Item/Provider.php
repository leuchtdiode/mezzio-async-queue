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
	 * @return Item[]
	 */
	public function filter(FilterChain $filterChain, ?OrderChain $orderChain = null): array
	{
		return $this->createDtos(
			$this->repository->filter($filterChain, $orderChain)
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
