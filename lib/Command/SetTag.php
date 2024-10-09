<?php

declare(strict_types=1);

namespace OCA\GroupfolderTags\Command;

use OC\Core\Command\Base;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class SetTag extends TagCommand {
	protected function configure(): void {
		$this
			->setName('groupfolder-tags:set')
			->setDescription('Update or add a tag (and optional tag value) to a groupfolder by id')
			->addArgument('groupfolder_id', InputArgument::REQUIRED, 'groupfolder id to add or update tag of')
			->addArgument('key', InputArgument::REQUIRED, 'key of tag')
			->addArgument('value', InputArgument::OPTIONAL, 'value of tag');
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$errors = [];
		
		$groupFolderId = $input->getArgument('groupfolder_id');
		$tagKey = $input->getArgument('key');
		$tagValue = $input->getArgument('value');

		if(!is_numeric($groupFolderId)) {
			$errors[] = "no group folder id provided";
		}

		if(is_null($tagKey) || $tagKey === "") {
			$errors[] = "no tag key provided";
		}

		if(empty($errors)) {
			$tag = $this->service->update((int)$groupFolderId, $tagKey, $tagValue);
			$this->writeTableInOutputFormat($input, $output, [$this->formatTagEntity($tag)]);

			return 0;
		} else {
			$output->writeln("Error: \n" . implode("\n", $errors));
			
			return 1;
		}
		
	}
}