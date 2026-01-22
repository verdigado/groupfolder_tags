<?php

namespace OCA\GroupfolderTags\Command;

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
			$groupfolders = $this->service->findAllIncludingGroupfolder($tagKey, $tagValue);


			if (empty($groupfolders)) {
				$output->writeln("<error>No matching Groupfolders</error>");
				return 1;
			}

			$results = [];

			foreach ($groupfolders as $groupfolder) {
				$results[] = $this->formatGroupfolderArray($groupfolder);
			}

			$this->writeTableInOutputFormat($input, $output, $results);

			return 0;
		} catch (Exception $e) {
			$output->writeln("<error>Exception \"{$e->getMessage()}\" at {$e->getFile()} line {$e->getLine()}</error>");
			return 1;
		}
	}
}
