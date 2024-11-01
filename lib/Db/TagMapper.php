<?php

namespace OCA\GroupfolderTags\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class TagMapper extends QBMapper {
	public const TABLENAME = 'groupfolder_tags';
	public const GROUP_FOLDERS_TABLENAME = "group_folders";

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLENAME, Tag::class);
	}

	/**
	 * @param int $groupFolderId
	 * @param string $tagKey
	 * @return Entity|Tag
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $groupFolderId, string $tagKey): Tag {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLENAME)
			->where($qb->expr()->eq('group_folder_id', $qb->createNamedParameter($groupFolderId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('tag_key', $qb->createNamedParameter($tagKey)));

		return $this->findEntity($qb);
	}

	/**
	 * @param int $groupFolderId
	 * @param string $tagKey
	 * @return Entity|Tag
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findIncludingGroupfolder(int $groupFolderId, string $tagKey): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLENAME, "t")
			->where($qb->expr()->eq('group_folder_id', $qb->createNamedParameter($groupFolderId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('tag_key', $qb->createNamedParameter($tagKey)))
			->leftJoin('t', self::GROUP_FOLDERS_TABLENAME, 'g', $qb->expr()->andX(
				$qb->expr()->eq('t.group_folder_id', 'g.folder_id'),
			));

		return $this->findOneQuery($qb);
	}

	/**
	 * @param string $tagKey
	 * @param string|null $tagValue
	 * @return array
	 */
	public function findAll(string $tagKey, ?string $tagValue): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLENAME)
			->where($qb->expr()->eq('tag_key', $qb->createNamedParameter($tagKey)));

		if(isset($tagValue)) {
			$qb->andWhere($qb->expr()->eq('tag_value', $qb->createNamedParameter($tagValue)));
		}

		return $this->findEntities($qb);
	}

	/**
	 * @param string $tagKey
	 * @param string|null $tagValue
	 * @return array
	 */
	public function findAllIncludingGroupfolder(string $tagKey, ?string $tagValue = null): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLENAME, alias: "t")
			->where($qb->expr()->eq('tag_key', $qb->createNamedParameter($tagKey)));

		if(isset($tagValue)) {
			$qb->andWhere($qb->expr()->eq('tag_value', $qb->createNamedParameter($tagValue)));
		}

		$qb->leftJoin('t', self::GROUP_FOLDERS_TABLENAME, 'g', $qb->expr()->andX(
			$qb->expr()->eq('t.group_folder_id', 'g.folder_id'),
		));

		return $qb->executeQuery()->fetchAll();
	}

	/**
	 * @param array $filters
	 * @param array|null $additionalReturnTags
	 * @return array
	 */
	private function findGroupfoldersWithTagsQueryBuilder(array $filters, array $additionalReturnTags = []): IQueryBuilder {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('g.mount_point', 'g.quota', 'g.acl')
			->selectAlias('g.folder_id', 'id')
			->from(self::GROUP_FOLDERS_TABLENAME, alias: "g");

		$index = 0;
		foreach($filters as $filter) {
			$alias = 'filter_' . $index;
			$joinConditions = [
				$qb->expr()->eq($alias . '.group_folder_id', 'g.folder_id'),
				$qb->expr()->eq($alias . '.tag_key', $qb->createNamedParameter($filter["key"]))
			];

			if(isset($filter["value"])) {
				$joinConditions[] = $qb->expr()->eq($alias . '.tag_value', $qb->createNamedParameter($filter["value"]));
			}

			$qb->innerJoin('g', self::TABLENAME, $alias, $qb->expr()->andX(...$joinConditions));

			if(isset($filter["includeInOutput"]) && $filter["includeInOutput"] === true) {
				$qb->selectAlias($alias . '.tag_value', $filter["key"]);
			}

			$index++;
		}

		$index = 0;
		foreach($additionalReturnTags as $additionalReturnTag) {
			$alias = 'additional_' . $index;

			$qb->leftJoin('g', self::TABLENAME, $alias, $qb->expr()->andX(
				$qb->expr()->eq($alias . '.group_folder_id', 'g.folder_id'),
				$qb->expr()->eq($alias . '.tag_key', $qb->createNamedParameter($additionalReturnTag))
			));

			$qb->selectAlias($alias . '.tag_value', $additionalReturnTag);

			$index++;
		}

		return $qb;
	}

	/**
	 * @param array $filters [key: string, (value: string), (includeInOutput: bool)] filter pairs, result is only included if 
	 * @param array|null $additionalReturnTags array of tags to return (if set, otherwise NULL)
	 * @return array
	 */
	public function findGroupfoldersWithTags(array $filters, array $additionalReturnTags = []): array {
		return $this->findGroupfoldersWithTagsQueryBuilder($filters, $additionalReturnTags)->executeQuery()->fetchAll();
	}

	/**
	 * @param array $filters [key: string, (value: string), (includeInOutput: bool)] filter pairs, result is only included if 
	 * @param array|null $additionalReturnTags array of tags to return (if set, otherwise NULL)
	 * @return array
	 */
	public function findGroupfoldersWithTagsGenerator(array $filters, array $additionalReturnTags = []): \Generator {
		$result = $this->findGroupfoldersWithTagsQueryBuilder($filters, $additionalReturnTags)->executeQuery();

		try {
			while ($row = $result->fetch()) {
				yield $row;
			}
		} finally {
			$result->closeCursor();
		}
	}

	/**
	 * @return Tag[]
	 * @throws Exception
	 */
	public function findByGroupFolderAndKey(string $groupFolderId, ?string $tagKey): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLENAME)
			->where($qb->expr()->eq('group_folder_id', $qb->createNamedParameter($groupFolderId, IQueryBuilder::PARAM_INT)));

		if(isset($tagKey)) {
			$qb->andWhere($qb->expr()->eq('tag_key', $qb->createNamedParameter($tagKey)));
		}

		return $this->findEntities($qb);
	}

	/**
	 * @throws Exception
	 */
	public function findGroupfoldersWithTag(string $tagKey, ?string $tagValue): array {
		$qb = $this->db->getQueryBuilder();
		$qb->selectDistinct('group_folder_id')
			->from(self::TABLENAME)
			->where($qb->expr()->eq('tag_key', $qb->createNamedParameter($tagKey)));

		if(isset($tagValue)) {
			$qb->andWhere($qb->expr()->eq('tag_value', $qb->createNamedParameter($tagValue)));
		}

		return $qb->executeQuery()->fetchAll(\PDO::FETCH_COLUMN);
	}
}
