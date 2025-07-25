<?php
namespace AsyncQueue\Item\Filter;

use Common\Db\Filter\Equals;

class Type extends Equals
{
	protected function getField(): string
	{
		return 't.type';
	}
}
