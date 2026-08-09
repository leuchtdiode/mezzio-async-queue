<?php
declare(strict_types=1);

namespace AsyncQueue\Queue;

class WorkerParams
{
	private ProcessParams $processParams;

	private float $sleepSeconds = 1.0;

	private ?int $maxRuntimeSeconds = null;

	public static function create(): static
	{
	    return new static();
	}

	public function getProcessParams(): ProcessParams
	{
		return $this->processParams;
	}

	public function setProcessParams(ProcessParams $processParams): WorkerParams
	{
		$this->processParams = $processParams;
		return $this;
	}

	public function getSleepSeconds(): float
	{
		return $this->sleepSeconds;
	}

	public function setSleepSeconds(float $sleepSeconds): WorkerParams
	{
		$this->sleepSeconds = $sleepSeconds;
		return $this;
	}

	public function getMaxRuntimeSeconds(): ?int
	{
		return $this->maxRuntimeSeconds;
	}

	public function setMaxRuntimeSeconds(?int $maxRuntimeSeconds): WorkerParams
	{
		$this->maxRuntimeSeconds = $maxRuntimeSeconds;
		return $this;
	}
}
