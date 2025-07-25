<?php
namespace AsyncQueue\Item\Filter;

use Common\Db\Filter\Equals;

class PayLoad extends Equals
{
	protected function getField(): string
	{
		return 't.payLoad';
	}
}
