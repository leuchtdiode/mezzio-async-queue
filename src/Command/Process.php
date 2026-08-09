<?php
namespace AsyncQueue\Command;

use AsyncQueue\Queue\Processor;
use AsyncQueue\Queue\ProcessParams;
use AsyncQueue\Queue\Worker;
use AsyncQueue\Queue\WorkerParams;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Process extends Command
{
	private const string TYPE         = 'type';
	private const string EXCLUDE_TYPE = 'exclude-type';
	private const string LIMIT        = 'limit';
	private const string WORKER       = 'worker';
	private const string SLEEP        = 'sleep';
	private const string MAX_RUNTIME  = 'max-runtime';

	private const float DEFAULT_SLEEP_SECONDS = 1.0;

	public function __construct(
		private readonly Processor $processor,
		private readonly Worker $worker
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setName('async-queue:process')
			->setDescription('Processes the pending async queue items')
			->addOption(
				self::TYPE,
				null,
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'Process only given types'
			)
			->addOption(
				self::EXCLUDE_TYPE,
				null,
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'Exclude given types'
			)
			->addOption(
				self::LIMIT,
				null,
				InputOption::VALUE_REQUIRED,
				'Limit items per run'
			)
			->addOption(
				self::WORKER,
				null,
				InputOption::VALUE_NONE,
				'Keep running and process items as soon as they show up, until a shutdown is requested'
			)
			->addOption(
				self::SLEEP,
				null,
				InputOption::VALUE_REQUIRED,
				'Seconds to wait before looking for items again when there was nothing to do, only used with --' . self::WORKER,
				self::DEFAULT_SLEEP_SECONDS
			)
			->addOption(
				self::MAX_RUNTIME,
				null,
				InputOption::VALUE_REQUIRED,
				'Stop after the given seconds, only used with --' . self::WORKER
			);
	}

	/**
	 * @throws Throwable
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$limit = ($input->getOption(self::LIMIT) ?? null);

		if ($limit)
		{
			$limit = (int)$limit;
		}

		$processParams = ProcessParams::create()
			->setTypes($input->getOption(self::TYPE) ?? [])
			->setExcludeTypes($input->getOption(self::EXCLUDE_TYPE) ?? [])
			->setLimit($limit);

		if (!$input->getOption(self::WORKER))
		{
			$this->processor->process($processParams);

			return self::SUCCESS;
		}

		$maxRuntime = ($input->getOption(self::MAX_RUNTIME) ?? null);

		if ($maxRuntime)
		{
			$maxRuntime = (int)$maxRuntime;
		}

		$this->worker->run(
			WorkerParams::create()
				->setProcessParams($processParams)
				->setSleepSeconds(
					(float)($input->getOption(self::SLEEP) ?? self::DEFAULT_SLEEP_SECONDS)
				)
				->setMaxRuntimeSeconds($maxRuntime)
		);

		return self::SUCCESS;
	}
}
