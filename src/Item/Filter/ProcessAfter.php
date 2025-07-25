<?php
namespace AsyncQueue\Item\Filter;

use Common\Db\Filter\Date;

class ProcessAfter extends Date
{
	protected function getColumn(): string
	{
		return 't.processAfter';
	}
}