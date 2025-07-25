<?php
namespace AsyncQueue\Command;

use AsyncQueue\Queue\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Process extends Command
{
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
			->setDescription('Processes the pending async queue items');
	}

	/**
	 * @throws Throwable
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->processor->process();

		return self::SUCCESS;
	}
}