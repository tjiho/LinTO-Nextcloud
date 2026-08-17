<?php

declare(strict_types=1);

namespace OCA\LinTO\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getId()
 * @method void setId(string $id)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getFileId()
 * @method void setFileId(string $fileId)
 * @method string getConversationId()
 * @method void setConversationId(string $conversationId)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getTranscript()
 * @method void setTranscript(string $transcript)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class TranscribeJob extends Entity {
	protected $userId;
	protected $fileId;
	protected $conversationId;
	protected $status;
	protected $transcript;
	protected $createdAt;
	protected $updatedAt;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('userId', 'string');
		$this->addType('fileId', 'integer');
		$this->addType('conversationId', 'string');
		$this->addType('status', 'string');
		$this->addType('transcript', 'text');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
	}
}
