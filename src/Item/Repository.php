<?php
namespace AsyncQueue\Item;

use Common\Db\EntityRepository;
use Ramsey\Uuid\Doctrine\UuidType;

class Repository extends EntityRepository
{
	/**
	 * Atomically moves a single pending item to processing.
	 * Returns false if the item is not pending anymore, e.g. because another worker claimed it first.
	 *
	 * The update is a DQL statement and bypasses the unit of work: the loaded entity and, more
	 * importantly, the original data the unit of work computes change sets against still say
	 * pending. Setting the status on the entity by hand is not enough - a later change back to
	 * pending (a retry, see Queue\Processor) would then equal the original data, be no change at all
	 * and never reach the database, leaving the row in processing forever. So the entity is
	 * refreshed from the database after the update, which also puts the original data right.
	 */
	public function claim(Entity $entity): bool
	{
		$entityManager = $this->getEntityManager();

		$affectedRows = $entityManager
			->createQuery(
				'UPDATE ' . Entity::class . ' i'
				. ' SET i.status = :newStatus'
				. ' WHERE i.id = :id AND i.status = :currentStatus'
			)
			->setParameter('newStatus', Status::PROCESSING)
			->setParameter('currentStatus', Status::PENDING)
			->setParameter('id', $entity->getId(), UuidType::NAME)
			->execute();

		if ($affectedRows !== 1)
		{
			return false;
		}

		$entityManager->refresh($entity);

		return true;
	}
}
