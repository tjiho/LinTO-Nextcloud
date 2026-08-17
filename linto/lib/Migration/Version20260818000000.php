<?php

declare(strict_types=1);

namespace OCA\LinTO\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version20260818000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('linto_transcribe_jobs')) {
			$table = $schema->createTable('linto_transcribe_jobs');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 11,
				'unsigned' => true,
			]);

			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
			]);

			$table->addColumn('file_id', 'integer', [
				'notnull' => true,
				'length' => 11,
				'unsigned' => true,
			]);

			$table->addColumn('conversation_id', 'string', [
				'notnull' => true,
				'length' => 255,
			]);

			$table->addColumn('status', 'string', [
				'notnull' => true,
				'length' => 50,
				'default' => 'pending',
			]);

			$table->addColumn('transcript', 'text', [
				'notnull' => false,
			]);

			$table->addColumn('created_at', 'datetime', [
				'notnull' => true,
			]);

			$table->addColumn('updated_at', 'datetime', [
				'notnull' => false,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['file_id'], 'linto_transcribe_jobs_file_id_idx');
			$table->addIndex(['conversation_id'], 'linto_transcribe_jobs_conversation_id_idx');
			$table->addIndex(['status'], 'linto_transcribe_jobs_status_idx');
			$table->addIndex(['user_id'], 'linto_transcribe_jobs_user_id_idx');
		}

		return $schema;
	}
}
