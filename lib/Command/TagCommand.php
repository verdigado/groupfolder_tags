<?php

namespace OCA\GroupfolderTags\Command;

use OCA\GroupfolderTags\Db\Tag;
use OCA\GroupfolderTags\Service\TagService;

use OCA\GroupFolders\Command\FolderCommand;
use OCA\GroupFolders\Folder\FolderManager;
use OCA\GroupFolders\Mount\MountProvider;
use OCA\GroupFolders\Mount\FolderStorageManager;

use OCP\Files\FileInfo;
use OCP\Files\IRootFolder;
use OCP\IDateTimeFormatter;
use OCP\Util;

abstract class TagCommand extends FolderCommand {

	public function __construct(
		FolderManager $folderManager,
		IRootFolder $rootFolder,
		MountProvider $mountProvider,
		FolderStorageManager $folderStorageManager,
		protected readonly TagService $service,
		private readonly IDateTimeFormatter $dateTimeFormatter,
	) {
		parent::__construct($folderManager, $rootFolder, $mountProvider, $folderStorageManager);
	}

	protected function formatTagEntity(Tag $tag) {
		return [
			"Groupfolder ID" => $tag->getGroupFolderId(),
			"Key" => $tag->getTagKey(),
			"Value" => $tag->getTagValue(),
			"Last Updated" => $this->dateTimeFormatter->formatDateTime($tag->getLastUpdatedTimestamp()),
		];
	}

	protected function formatTagEntities($tags) {
		return array_map($this->formatTagEntity(...), $tags);
	}

	protected function formatGroupfolderArray(array $groupfolder) {
		$quota = $groupfolder["quota"];

		if($quota === FolderManager::SPACE_DEFAULT) {
			$humanQuota = "Default";
		} else if($quota === FileInfo::SPACE_UNLIMITED) {
			$humanQuota = "Unlimited";
		} else {
			$humanQuota = Util::humanFileSize($quota) . " (" . $quota . " bytes)";
		}

		return [
			"Groupfolder ID" => $groupfolder["folder_id"],
			"Mount Point" => $groupfolder["mount_point"],
			"Quota" => $humanQuota,
			"ACLs enabled" => !!$groupfolder["acl"] ? "yes" : "no",
		];
	}
}
