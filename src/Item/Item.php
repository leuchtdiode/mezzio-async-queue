<?php
namespace AsyncQueue\Item;

class Item
{
	private Entity $entity;

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
	}

	public function getType(): string
	{
		return $this->entity->getType();
	}

	public function getPayLoad(): array
	{
		return $this->entity->getPayLoad() ?? [];
	}

	public function getEntity(): Entity
	{
		return $this->entity;
	}
}