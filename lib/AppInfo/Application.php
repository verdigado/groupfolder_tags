<?php

namespace OCA\GroupfolderTags\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'groupfolder_tags';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}