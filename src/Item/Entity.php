<?php
namespace AsyncQueue\Item;

use Common\Db\Entity as DbEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'asyncqueue_items')]
class Entity implements DbEntity
{
	#[ORM\Id]
	#[ORM\Column(type: 'uuid')]
	private UuidInterface $id;

	#[ORM\Column(type: 'string', length: 25)]
	private string $status;

	#[ORM\Column(type: 'string', length: 150)]
	private string $type;

	#[ORM\Column(type: 'json', nullable: true)]
	private ?array $payLoad = null;

	#[ORM\Column(type: 'datetime')]
	private DateTime $createdDate;

	#[ORM\Column(type: 'datetime')]
	private DateTime $processAfter;

	/**
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->id          = Uuid::uuid4();
		$this->status      = Status::PENDING;
		$this->createdDate = new DateTime();
	}

	public function getId(): UuidInterface
	{
		return $this->id;
	}

	public function setId(UuidInterface $id): void
	{
		$this->id = $id;
	}

	public function getStatus(): string
	{
		return $this->status;
	}

	public function setStatus(string $status): void
	{
		$this->status = $status;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): void
	{
		$this->type = $type;
	}

	public function getPayLoad(): ?array
	{
		return $this->payLoad;
	}

	public function setPayLoad(?array $payLoad): void
	{
		$this->payLoad = $payLoad;
	}

	public function getCreatedDate(): DateTime
	{
		return $this->createdDate;
	}

	public function setCreatedDate(DateTime $createdDate): void
	{
		$this->createdDate = $createdDate;
	}

	public function getProcessAfter(): DateTime
	{
		return $this->processAfter;
	}

	public function setProcessAfter(DateTime $processAfter): void
	{
		$this->processAfter = $processAfter;
	}
}