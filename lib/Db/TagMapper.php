<?php

namespace OCA\GroupfolderTags\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class TagMapper extends QBMapper {
	public const TABLENAME = 'groupfolder_tags';

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
}