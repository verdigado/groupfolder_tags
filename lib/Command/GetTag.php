<?php

namespace OCA\GroupfolderTags\Command;

use OCA\GroupfolderTags\Db\Tag;
use OCP\DB\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetTag extends TagCommand {
	protected function configure(): void {
		$this
			->setName('groupfolder-tags:get')
			->setDescription('Get single tag value by Groupfolder ID and key. Omit key to get all tags.')
			->addArgument('folder_id', InputArgument::REQUIRED, 'Groupfolder ID of the tag')
			->addArgument('key', InputArgument::OPTIONAL, 'Key of the tag');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$groupFolderId = $input->getArgument('folder_id');
		$tagKey = $input->getArgument('key');

		if (!$this->getFolder($input, $output)) {
			return 1;
		}

		try {
			$tags = $this->service->findByGroupFolderAndKey($groupFolderId, $tagKey);

			if (empty($tags)) {
				$output->writeln("<error>Could not find tag for Groupfolder</error>");
				return 1;
			}

			if (isset($tagKey)) {
				/** @var Tag $tag */
				$tag = current($tags);
				$output->writeln($tag->getTagValue());
				return 0;
			}

			foreach ($tags as $tag) {
				$output->writeln(json_encode($tag));
			}
			return 0;
		} catch (Exception $e) {
			$output->writeln("<error>Exception \"{$e->getMessage()}\" at {$e->getFile()} line {$e->getLine()}</error>");
			return 1;
		}
	}
}
