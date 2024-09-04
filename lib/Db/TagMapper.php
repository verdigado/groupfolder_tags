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
	public function findIncludingGroupfolder(int $groupFolderId, string $tagKey): Tag {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLENAME, "t")
			->where($qb->expr()->eq('group_folder_id', $qb->createNamedParameter($groupFolderId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('tag_key', $qb->createNamedParameter($tagKey)))
			->leftJoin('t', self::GROUP_FOLDERS_TABLENAME, 'g', $qb->expr()->andX(
				$qb->expr()->eq('t.group_folder_id', 'g.id'),
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
	public function findFolder(string $tagKey, ?string $tagValue): array {
		$qb = $this->db->getQueryBuilder();
		$qb->selectDistinct('group_folder_id')
			->from(self::TABLENAME)
			->where($qb->expr()->eq('tag_key', $qb->createNamedParameter($tagKey)));

		if(isset($tagValue)) {
			$qb->andWhere($qb->expr()->eq('tag_value', $qb->createNamedParameter($tagValue)));
		}

		return $qb->executeQuery()->fetchAll();
	}
}
