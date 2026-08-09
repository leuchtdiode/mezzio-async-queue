<?php
namespace AsyncQueue\Item;

use Common\Db\EntityRepository;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\UuidInterface;

class Repository extends EntityRepository
{
	/**
	 * Atomically moves a single pending item to processing.
	 * Returns false if the item is not pending anymore, e.g. because another worker claimed it first.
	 */
	public function claim(UuidInterface $id): bool
	{
		$affectedRows = $this
			->getEntityManager()
			->createQuery(
				'UPDATE ' . Entity::class . ' i'
				. ' SET i.status = :newStatus'
				. ' WHERE i.id = :id AND i.status = :currentStatus'
			)
			->setParameter('newStatus', Status::PROCESSING)
			->setParameter('currentStatus', Status::PENDING)
			->setParameter('id', $id, UuidType::NAME)
			->execute();

		return $affectedRows === 1;
	}
}
