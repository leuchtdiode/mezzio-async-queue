<?php
namespace AsyncQueue\Item;

class ProcessResult
{
	private ?bool  $success        = null;
	private ?int   $retryInSeconds = null;
	private bool   $changePayload  = false;
	private ?array $newPayLoad     = null;

	public function isSuccess(): ?bool
	{
		return $this->success;
	}

	public function setSuccess(?bool $success): void
	{
		$this->success = $success;
	}

	public function getRetryInSeconds(): ?int
	{
		return $this->retryInSeconds;
	}

	public function setRetryInSeconds(?int $retryInSeconds): void
	{
		$this->retryInSeconds = $retryInSeconds;
	}

	public function isChangePayload(): bool
	{
		return $this->changePayload;
	}

	public function getNewPayLoad(): ?array
	{
		return $this->newPayLoad;
	}

	public function setNewPayLoad(?array $newPayLoad): void
	{
		$this->newPayLoad    = $newPayLoad;
		$this->changePayload = true;
	}
}