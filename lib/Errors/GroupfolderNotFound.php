<?php

namespace OCA\GroupfolderTags\Errors;

class GroupfolderNotFound extends NotFoundException {
    public function __construct($id) {
		parent::__construct("groupfolder", ["id" => $id]);
	}
}