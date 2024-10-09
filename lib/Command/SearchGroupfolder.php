<?php

namespace OCA\GroupfolderTags\Command;

use OCA\GroupfolderTags\Errors\GroupfolderNotFound;

use OCP\DB\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindGroupfolders extends TagCommand {
	protected function configure(): void {
		$this
			->setName('groupfolder-tags:find-groupfolders')
			->setDescription('Get all groupfolders with tag key optionally filtered by tag value.')
			->addArgument('key', InputArgument::REQUIRED, 'Key of the tag')
			->addArgument('value', InputArgument::OPTIONAL, 'Value of the tag');
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$tagKey = $input->getArgument('key');
		$tagValue = $input->getArgument('value');

		try {
			$groupfolderIds = $this->service->findGroupfoldersWithTag($tagKey, $tagValue);

			if (empty($groupfolderIds)) {
				$output->writeln("<error>No matching Groupfolders</error>");
				return 1;
			}

			$results = [];

			foreach ($groupfolderIds as $groupfolderId) {
				try {
					$results[] = $this->formatGroupfolder($groupfolderId);
				} catch (GroupfolderNotFound $e) {
					$output->writeln("<error>{$e->getMessage()}</error>");
				}
			}

			$this->writeTableInOutputFormat($input, $output, $results);

			return 0;
		} catch (Exception $e) {
			$output->writeln("<error>Exception \"{$e->getMessage()}\" at {$e->getFile()} line {$e->getLine()}</error>");
			return 1;
		}
	}
}
