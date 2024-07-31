<?php

namespace OCA\GroupfolderTags\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Tag extends Entity implements JsonSerializable {
	protected $groupFolderId;
	protected $tagKey;
	protected $tagValue;
	protected $lastUpdatedTimestamp;

	public function __construct() {
		$this->addType('groupFolderId','integer');
		$this->addType('lastUpdatedTimestamp','integer');
	}

	public function jsonSerialize(): array {
		return [
			'groupFolderId' => $this->groupFolderId,
			'tagKey' => $this->tagKey,
			'tagValue' => $this->tagValue,
			'lastUpdatedTimestamp' => $this->lastUpdatedTimestamp,
		];
	}
}