<?php

declare(strict_types=1);

namespace OCA\GroupfolderTags\Command;

use OC\Core\Command\Base;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use OCA\GroupfolderTags\Service\TagService;

class Tag extends Base {
	public function __construct(
		private TagService $service,
	) {
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
				'key',
				null,
				InputOption::VALUE_REQUIRED,
				'Set tag key to given value'
			)
			->addOption(
				'value',
				null,
				InputOption::VALUE_OPTIONAL,
				'Set tag value to given value'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$errors = [];
		
		$groupFolderId = $input->getOption('groupfolder_id');
		$tagKey = $input->getOption('key');
		$tagValue = $input->getOption('value');

		if(!is_numeric($groupFolderId)) {
			$errors[] = "no group folder id provided";
		}

		if(is_null($tagKey) || $tagKey === "") {
			$errors[] = "no tag key provided";
		}

		if(empty($errors)) {
			$output->writeln($groupFolderId . " " . $tagKey . " " . $tagValue);
			$output->writeln(json_encode($this->service->update((int)$groupFolderId, $tagKey, $tagValue)));
			return 0;
		} else {
			$output->writeln("Error: \n" . implode("\n", $errors));
			return 1;
		}
		
	}
}