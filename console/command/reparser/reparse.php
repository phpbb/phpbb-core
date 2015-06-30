<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\console\command\reparser;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class reparse extends \phpbb\console\command\command
{
	/**
	* @var \phpbb\di\service_collection
	*/
	protected $reparsers;

	/**
	* Constructor
	*
	* @param \phpbb\user $user
	* @param \phpbb\di\service_collection $reparser_collection
	*/
	public function __construct(\phpbb\user $user, \phpbb\di\service_collection $reparsers)
	{
		require_once __DIR__ . '/../../../../includes/functions_content.php';

		$this->reparsers = $reparsers;
		parent::__construct($user);
	}

	/**
	* Sets the command name and description
	*
	* @return null
	*/
	protected function configure()
	{
		$this
			->setName('reparser:reparse')
			->setDescription($this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE'))
			->addArgument('reparser-name', InputArgument::OPTIONAL, $this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_ARG_1'))
			->addOption(
				'range-min',
				null,
				InputOption::VALUE_REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_OPT_RANGE_MIN'),
				1
			)
			->addOption(
				'range-max',
				null,
				InputOption::VALUE_REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_OPT_RANGE_MAX')
			)
			->addOption(
				'range-size',
				null,
				InputOption::VALUE_REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_OPT_RANGE_SIZE'),
				100
			);
		;
	}

	/**
	* Executes the command reparser:reparse
	*
	* @param InputInterface $input
	* @param OutputInterface $output
	* @return integer
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('reparser-name');
		if (isset($name))
		{
			// Allow "post_text" to be an alias for "text_reparser.post_text"
			if (!isset($this->reparsers[$name]))
			{
				$name = 'text_reparser.' . $name;
			}
			$this->reparse($input, $output, $name);
		}
		else
		{
			foreach ($this->reparsers as $name => $service)
			{
				$this->reparse($input, $output, $name);
			}
		}

		return 0;
	}

	/**
	* Reparse all text handled by given reparser within given range
	*
	* @param InputInterface $input
	* @param OutputInterface $output
	* @param string $name Reparser name
	* @return null
	*/
	protected function reparse(InputInterface $input, OutputInterface $output, $name)
	{
		$reparser = $this->reparsers[$name];

		// Start at range-max if specified or at the highest ID otherwise
		$max  = (is_null($input->getOption('range-max'))) ? $reparser->get_max_id() : $input->getOption('range-max');
		$min  = $input->getOption('range-min');
		$size = $input->getOption('range-size');

		$output->writeLn($this->user->lang('CLI_REPARSER_REPARSE_REPARSING', str_replace('text_reparser.', '', $name), $min, $max) . '</info>');

		$progress = new ProgressBar($output, $max + 1 - $min);
		$progress->start();

		// Start from $max and decrement $current by $size until we reach $min
		$current = $max;
		while ($current >= $min)
		{
			$start = max($min, $current + 1 - $size);
			$end   = max($min, $current);
			$reparser->reparse_range($start, $end);

			$current = $start - 1;
			$progress->setProgress($max + 1 - $start);
		}
		$progress->finish();

		// The progress bar does not seem to end with a newline so we add one manually
		$output->writeLn('');
	}
}