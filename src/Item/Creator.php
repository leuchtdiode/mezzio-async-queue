<?php
namespace AsyncQueue\Item;

class Creator
{
	public function byEntity(Entity $entity): Item
	{
		return new Item($entity);
	}
}