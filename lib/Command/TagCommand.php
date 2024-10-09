<?php

namespace OCA\GroupfolderTags\Command;

use OCA\GroupfolderTags\Db\Tag;
use OCA\GroupfolderTags\Service\TagService;
use OCA\GroupfolderTags\Errors\GroupfolderNotFound;

use OCA\GroupFolders\Command\FolderCommand;
use OCA\GroupFolders\Folder\FolderManager;
use OCA\GroupFolders\Mount\MountProvider;


use OCP\Files\IRootFolder;
use OCP\IDateTimeFormatter;

abstract class TagCommand extends FolderCommand {
	private int $rootFolderNumericStorageId;

	public function __construct(
		FolderManager $folderManager,
		IRootFolder $rootFolder,
		MountProvider $mountProvider,
		protected readonly TagService $service,
		private readonly IDateTimeFormatter $dateTimeFormatter,
	) {
		parent::__construct($folderManager, $rootFolder, $mountProvider);

		
	}

	protected function getRootFolderNumericStorageId() {
		if(!isset($this->rootFolderNumericStorageId)) {
			$this->rootFolderNumericStorageId = $this->rootFolder->getMountPoint()->getNumericStorageId();
		}
		
		return $this->rootFolderNumericStorageId;
	}

	protected function formatTagEntity(Tag $tag) {
		return [
			"Groupfolder Id" => $tag->getGroupFolderId(),
			"Key" => $tag->getTagKey(),
			"Value" => $tag->getTagValue(),
			"Last Updated" => $this->dateTimeFormatter->formatDateTime($tag->getLastUpdatedTimestamp()),
		];
	}

	protected function formatTagEntities($tags) {
		return array_map($this->formatTagEntity(...), $tags);
	}

	protected function formatGroupfolder($groupfolderId) {
		$groupfolder = $this->folderManager->getFolder($groupfolderId, $this->getRootFolderNumericStorageId());

		if ($groupfolder === false) {
			throw new GroupfolderNotFound($groupfolderId);
		}

		return [
			"Groupfolder Id" => $groupfolder["id"],
			"Mount Point" => $groupfolder["mount_point"],
			"Quota" => $groupfolder["quota"],
			"Size" => $groupfolder["size"],
			"ACL" => $groupfolder["acl"],
		];
	}
}
