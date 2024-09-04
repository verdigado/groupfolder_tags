<?php

namespace OCA\GroupfolderTags\Command;

use OCA\GroupFolders\Command\FolderCommand;
use OCA\GroupFolders\Folder\FolderManager;
use OCA\GroupFolders\Mount\MountProvider;
use OCA\GroupfolderTags\Service\TagService;
use OCP\Files\IRootFolder;

abstract class TagCommand extends FolderCommand {
	public function __construct(
		protected readonly TagService $service,
		FolderManager $folderManager,
		IRootFolder $rootFolder,
		MountProvider $mountProvider
	) {
		parent::__construct($folderManager, $rootFolder, $mountProvider);
	}
}
