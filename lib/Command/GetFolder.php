<?php

namespace OCA\GroupfolderTags\Command;

use OCP\DB\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetFolder extends TagCommand {
	protected function configure(): void {
		$this
			->setName('groupfolder-tags:get-folder')
			->setDescription('Get Groupfolders with tag key optionally filtered by tag value.')
			->addArgument('key', InputArgument::REQUIRED, 'Key of the tag')
			->addArgument('value', InputArgument::OPTIONAL, 'Value of the tag');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$tagKey = $input->getArgument('key');
		$tagValue = $input->getArgument('value');

		try {
			$results = $this->service->findFolder($tagKey, $tagValue);

			if (empty($results)) {
				$output->writeln("<error>Could not find Groupfolders for tag</error>");
				return 1;
			}

			foreach ($results as $result) {
				$folder = $this->folderManager->getFolder($result['group_folder_id'], $this->rootFolder->getMountPoint()->getNumericStorageId());
				if ($folder === false) {
					$output->writeln("<error>Folder not found: {$result['group_folder_id']}</error>");
					continue;
				}
				$output->writeln(json_encode($folder));
			}
			return 0;
		} catch (Exception $e) {
			$output->writeln("<error>Exception \"{$e->getMessage()}\" at {$e->getFile()} line {$e->getLine()}</error>");
			return 1;
		}
	}
}
