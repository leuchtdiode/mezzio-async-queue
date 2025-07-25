<?php
namespace AsyncQueue\Item\Order;

use Common\Db\Order\AscOrDesc;

class ProcessAfter extends AscOrDesc
{
	protected function getField(): string
	{
		return 't.processAfter';
	}
}