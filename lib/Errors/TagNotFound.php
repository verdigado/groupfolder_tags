<?php

namespace OCA\GroupfolderTags\Errors;

class TagNotFound extends NotFoundException {
    public function __construct($groupFolderId, $tagKey) {
		parent::__construct(OCA\GroupfolderTags\Db\Tag::class, ["groupFolderId" => $groupFolderId, "tagKey" => $tagKey]);
	}
}