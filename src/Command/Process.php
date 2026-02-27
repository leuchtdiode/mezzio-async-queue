<?php
namespace AsyncQueue\Command;

use AsyncQueue\Queue\Processor;
use AsyncQueue\Queue\ProcessParams;
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

	public function __construct(
		private readonly Processor $processor
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

		$this->processor->process(
			ProcessParams::create()
				->setTypes($input->getOption(self::TYPE) ?? [])
				->setExcludeTypes($input->getOption(self::EXCLUDE_TYPE) ?? [])
				->setLimit($limit)
		);

		return self::SUCCESS;
	}
}