<?php
declare(strict_types=1);

namespace AsyncQueue\Queue;

class ProcessParams
{
	/**
	 * @var string[]
	 */
	private array $types;

	/**
	 * @var string[]
	 */
	private array $excludeTypes;

	public static function create(): static
	{
	    return new static();
	}

	/**
	 * @return string[]
	 */
	public function getTypes(): array
	{
		return $this->types;
	}

	/**
	 * @param string[] $types
	 */
	public function setTypes(array $types): ProcessParams
	{
		$this->types = $types;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getExcludeTypes(): array
	{
		return $this->excludeTypes;
	}

	/**
	 * @param string[] $excludeTypes
	 */
	public function setExcludeTypes(array $excludeTypes): ProcessParams
	{
		$this->excludeTypes = $excludeTypes;
		return $this;
	}
}