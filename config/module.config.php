<?php
namespace AsyncQueue;

use AsyncQueue\Command\Process as Process;
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
			'async_queue_entities' => [
				'class' => AttributeDriver::class,
				'cache' => 'array',
				'paths' => [ __DIR__ . '/../src' ],
			],
			'orm_default'          => [
				'class'   => AttributeDriver::class,
				'drivers' => [
					'AsyncQueue' => 'async_queue_entities',
				],
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
];