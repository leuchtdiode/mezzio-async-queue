<?php
namespace AsyncQueue\Item;

interface Processor
{
	public function process(ProcessData $data): ProcessResult;
}