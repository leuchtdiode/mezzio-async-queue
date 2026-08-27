<?php
namespace AsyncQueue;

use AsyncQueue\Command\Process as Process;
use AsyncQueue\Health\StaleItemsCheck;
use AsyncQueue\Shutdownable\NoProcessingItem;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Ramsey\Uuid\Doctrine\UuidType;

return [

	'async-queue' => [
		'processors' => [],
		'monitoring' => [
			'staleItems' => [
				'thresholdMinutes' => 60,
			],
		],
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
		'shutdown' => [
			'checkers' => [
				NoProcessingItem::class,
			],
		],
	],

	// merged into the application config, leuchtdiode/mezzio-monitoring is only a suggestion
	// and nothing reads this key when it is not installed
	'monitoring' => [
		'health' => [
			'checkers' => [
				StaleItemsCheck::class,
			],
		],
	],
];