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

	public function findAllIncludingGroupfolder(string $tagKey, ?string $tagValue = null): array {
		return $this->mapper->findAllIncludingGroupfolder($tagKey, $tagValue);
	}

	public function findGroupfoldersWithTags(array $filters, array $additionalReturnTags = []): array {
		return $this->mapper->findGroupfoldersWithTags($filters, $additionalReturnTags);
	}

	public function findGroupfoldersWithTagsGenerator(array $filters, array $additionalReturnTags = []): \Generator {
		return $this->mapper->findGroupfoldersWithTagsGenerator($filters, $additionalReturnTags);
	}

	private function handleException(Exception $e, int $groupFolderId, string $tagKey): void {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new TagNotFound($groupFolderId, $tagKey);
		} else {
			throw $e;
		}
	}

	public function find(int $groupFolderId, string $tagKey): Tag {
		try {
			return $this->mapper->find($groupFolderId, $tagKey);
		} catch (Exception $e) {
			$this->handleException($e, $groupFolderId, $tagKey);
		}
	}

	public function findGroupfolderWithTags(int $groupFolderId, array $filters, array $additionalReturnTags = []): ?array {
		try {
			return $this->mapper->findGroupfolderWithTags($groupFolderId, $filters, $additionalReturnTags);
		} catch (DoesNotExistException $e) {
			return null;
		}
	}

	public function update(int $groupFolderId, string $key, ?string $value = null): Tag {
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

	public function delete(int $groupFolderId, string $tagKey): Tag {
		try {
			$tag = $this->mapper->find($groupFolderId, $tagKey);
			$this->mapper->delete($tag);
			return $tag;
		} catch (Exception $e) {
			$this->handleException($e, $groupFolderId, $tagKey);
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
	public function findGroupfoldersWithTag(string $key, ?string $value): array {
		return $this->mapper->findGroupfoldersWithTag($key, $value);
	}
}
