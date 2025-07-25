<?php
namespace AsyncQueue\Item;

class ProcessData
{
	private array $payLoad;

	public function __construct(array $payLoad)
	{
		$this->payLoad = $payLoad;
	}

	public function getPayLoad(): array
	{
		return $this->payLoad;
	}
}