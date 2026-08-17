<?php

declare(strict_types=1);

namespace OCA\LinTO\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TranscribeJobMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'linto_transcribe_jobs', TranscribeJob::class);
	}

	/**
	 * Find job by conversation ID
	 */
	public function findByConversationId(string $conversationId): ?TranscribeJob {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('conversation_id', $qb->createNamedParameter($conversationId)));

		return $this->findEntity($qb);
	}

	/**
	 * Find job by ID
	 */
	public function find(int $id): ?TranscribeJob {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, \PDO::PARAM_INT)));

		try {
			return $this->findEntity($qb);
		} catch (\OCP\AppFramework\Db\DoesNotExistException|\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
			return null;
		}
	}
}
