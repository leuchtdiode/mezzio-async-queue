<?php
namespace AsyncQueue\Item;

class Status
{
	const PENDING    = 'pending';
	const PROCESSING = 'processing';
	const FAILED     = 'failed';
	const SUCCESS    = 'success';
}
