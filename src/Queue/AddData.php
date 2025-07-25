<?php
namespace AsyncQueue\Queue;

use DateTime;

class AddData
{
	private string    $type;
	private array     $payLoad;
	private ?DateTime $processAfter        = null;
	private bool      $addOnlyIfNotInQueue = false;

	public static function create(): self
	{
		return new self();
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): AddData
	{
		$this->type = $type;
		return $this;
	}

	public function getPayLoad(): array
	{
		return $this->payLoad;
	}

	public function setPayLoad(array $payLoad): AddData
	{
		$this->payLoad = $payLoad;
		return $this;
	}

	public function getProcessAfter(): ?DateTime
	{
		return $this->processAfter;
	}

	public function setProcessAfter(?DateTime $processAfter): AddData
	{
		$this->processAfter = $processAfter;
		return $this;
	}

	public function isAddOnlyIfNotInQueue(): bool
	{
		return $this->addOnlyIfNotInQueue;
	}

	public function setAddOnlyIfNotInQueue(bool $addOnlyIfNotInQueue): AddData
	{
		$this->addOnlyIfNotInQueue = $addOnlyIfNotInQueue;
		return $this;
	}
}
