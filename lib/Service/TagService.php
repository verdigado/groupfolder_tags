<?php

namespace OCA\GroupfolderTags\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\GroupfolderTags\Errors\TagNotFound;

use OCA\GroupfolderTags\Db\Tag;
use OCA\GroupfolderTags\Db\TagMapper;

class TagService {
	public function __construct(
		private TagMapper $mapper
	) {
	}

	public function findAllWithTagKey(string $tagKey, ?string $tagValue): array {
		return $this->mapper->findAll($tagKey, $tagValue);
	}

	private function handleException(Exception $e): void {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new TagNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $groupFolderId, string $tagKey): Tag {
		try {
			return $this->mapper->find($groupFolderId, $tagKey);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $groupFolderId, string $key, ?string $value): Tag {
		try {
			$tag = $this->find($groupFolderId, $key);
			$tagExists = True;
		} catch(TagNotFound $error) {
			$tag = new Tag();
			$tag->setGroupFolderId($groupFolderId);
			$tag->setTagKey($key);
			$tagExists = False;
		}

		$tag->setTagValue($value);

		$tag->setLastUpdatedTimestamp(time());

		if($tagExists) {
			return $this->mapper->update($tag);
		} else {
			return $this->mapper->insert($tag);
		}
	}

	public function delete(int $groupFolderId, string $key): Tag {
		try {
			$tag = $this->mapper->find($groupFolderId, $key);
			$this->mapper->delete($tag);
			return $tag;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * @return Tag[]
	 * @throws \OCP\DB\Exception
	 */
	public function findByGroupFolderAndKey(int $groupFolderId, ?string $key): array {
		return $this->mapper->findByGroupFolderAndKey($groupFolderId, $key);
	}

	/**
	 * @throws \OCP\DB\Exception
	 */
	public function findFolder(string $key, ?string $value): array {
		return $this->mapper->findFolder($key, $value);
	}
}
