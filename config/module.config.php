<?php
namespace AsyncQueue;

use AsyncQueue\Command\Process as Process;
use AsyncQueue\Shutdownable\NoProcessingItem;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Ramsey\Uuid\Doctrine\UuidType;

return [

	'async-queue' => [
		'processors' => [],
	],

	'doctrine' => [
		'types'  => [
			UuidType::NAME => UuidType::class,
		],
		'driver' => [
			'orm_default' => [
				'class' => AttributeDriver::class,
				'paths' => [ __DIR__ . '/../src' ],
			],
		],
	],

	'console' => [
		'commands' => [
			Process::class,
		],
	],

	'dependencies' => [
		'abstract_factories' => [
			DefaultFactory::class,
		],
	],

	'common' => [
		'shutdownable' => [
			'checkers' => [
				NoProcessingItem::class,
			],
		],
	],
];