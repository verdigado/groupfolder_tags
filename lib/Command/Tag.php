<?php

declare(strict_types=1);

namespace OCA\GroupfolderTags\Command;

use OC\Core\Command\Base;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Tag extends Base {
	public function __construct() {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('groupfolder-tags:update')
			->setDescription('Update or add a tag (and optional tag value) to a groupfolder by id')
			->addOption(
				'groupfolder_id',
				null,
				InputOption::VALUE_REQUIRED,
				'Add or update tag of the given groupfolder id'
			)
			->addOption(
				'tag_key',
				null,
				InputOption::VALUE_REQUIRED,
				'Set tag key to given value'
			)
			->addOption(
				'tag_value',
				null,
				InputOption::VALUE_OPTIONAL,
				'Set tag value to given value'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$groupFolderId = $input->getOption('groupfolder_id');
		$tagKey = $input->getOption('tag_key');
		$tagValue = $input->getOption('tag_value');

		$output->writeln($groupFolderId . " " . $tagKey . " " . $tagValue);
		return 0;
	}
}