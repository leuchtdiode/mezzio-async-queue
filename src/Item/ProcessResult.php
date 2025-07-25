<?php
namespace AsyncQueue\Item;

class ProcessResult
{
	private ?bool $success = null;

	private ?int $retryInSeconds = null;

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
}