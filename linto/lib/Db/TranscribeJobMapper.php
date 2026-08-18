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
	 * Terminal statuses: a job in one of these states is no longer running
	 * and should not block a new transcription attempt on the same file.
	 */
	private const TERMINAL_STATUSES = ['done', 'error', 'timeout'];

	/**
	 * Find the most recent non-finished job for a file, if any.
	 */
	public function findActiveByFileId(int $fileId): ?TranscribeJob {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('file_id', $qb->createNamedParameter($fileId, \PDO::PARAM_INT)))
			->andWhere($qb->expr()->notIn('status', $qb->createNamedParameter(self::TERMINAL_STATUSES, \OCP\DB\QueryBuilder\IQueryBuilder::PARAM_STR_ARRAY)))
			->orderBy('created_at', 'DESC')
			->setMaxResults(1);

		try {
			return $this->findEntity($qb);
		} catch (\OCP\AppFramework\Db\DoesNotExistException|\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
			return null;
		}
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
