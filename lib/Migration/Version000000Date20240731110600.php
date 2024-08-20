<?php

declare(strict_types=1);

namespace OCA\GroupfolderTags\Migration;

use Closure;
use OCP\DB\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000000Date20240731110600 extends SimpleMigrationStep {
	public const GROUP_FOLDERS_TABLE = "group_folders";
	public const GROUP_FOLDER_TAGS_TABLE = "groupfolder_tags";

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable(self::GROUP_FOLDER_TAGS_TABLE)) {
			$table = $schema->createTable(self::GROUP_FOLDER_TAGS_TABLE);
			$table->addColumn('id', Types::INTEGER, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('group_folder_id', Types::BIGINT, [
				'notnull' => true,
				'length' => 20,
			]);
			$table->addColumn('tag_key', Types::STRING, [
				'notnull' => true,
				'length' => 50,
			]);
			$table->addColumn('tag_value', Types::STRING, [
				'notnull' => false,
				'length' => 200,
			]);
			$table->addColumn('last_updated_timestamp', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['group_folder_id', 'tag_key'], "groupfolder_tags_group_folder_id_tag_key_index");
			$table->addIndex(['group_folder_id'], 'groupfolder_tags_group_folder_id_index');
			$table->addForeignKeyConstraint(
				$schema->getTable(self::GROUP_FOLDERS_TABLE),
				['group_folder_id'],
				['folder_id'],
				['onDelete' => 'CASCADE'],
				'groupfolder_tags_group_folder_id_fk');
		}

		return $schema;
	}
}