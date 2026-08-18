<?php

declare(strict_types=1);

namespace OCA\LinTO\Service;

use OCP\SystemTag\ISystemTag;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTagObjectMapper;
use OCP\SystemTag\TagAlreadyExistsException;
use OCP\SystemTag\TagNotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Flags files with read-only system tags reflecting the state of their LinTO
 * transcription job ("in progress" / "failed"), so that state is visible
 * directly in the Files app (list pill + details panel) without any custom
 * frontend code.
 */
class TranscriptionTagService {
	private const IN_PROGRESS_TAG_NAME = 'Transcription LinTO en cours';
	private const IN_PROGRESS_TAG_COLOR = 'e9a23b';
	private const FAILED_TAG_NAME = 'Transcription LinTO échouée';
	private const FAILED_TAG_COLOR = 'c9403a';
	private const OBJECT_TYPE = 'files';

	public function __construct(
		private ISystemTagManager $tagManager,
		private ISystemTagObjectMapper $tagObjectMapper,
		private LoggerInterface $logger,
	) {
	}

	private function getOrCreateTag(string $name, string $color): ISystemTag {
		try {
			return $this->tagManager->getTag($name, true, false);
		} catch (TagNotFoundException $e) {
			try {
				$tag = $this->tagManager->createTag($name, true, false);
			} catch (TagAlreadyExistsException $e) {
				// Race with another request creating it at the same time.
				return $this->tagManager->getTag($name, true, false);
			}

			try {
				$this->tagManager->updateTag(
					$tag->getId(),
					$tag->getName(),
					$tag->isUserVisible(),
					$tag->isUserAssignable(),
					$color,
				);
			} catch (\Exception $e) {
				// Cosmetic only, don't fail tagging over a color update.
				$this->logger->warning('TranscriptionTagService: failed to set tag color: ' . $e->getMessage());
			}

			return $tag;
		}
	}

	private function getInProgressTag(): ISystemTag {
		return $this->getOrCreateTag(self::IN_PROGRESS_TAG_NAME, self::IN_PROGRESS_TAG_COLOR);
	}

	private function getFailedTag(): ISystemTag {
		return $this->getOrCreateTag(self::FAILED_TAG_NAME, self::FAILED_TAG_COLOR);
	}

	public function markInProgress(int $fileId): void {
		try {
			$tag = $this->getInProgressTag();
			$this->tagObjectMapper->assignTags((string)$fileId, self::OBJECT_TYPE, $tag->getId());
		} catch (\Exception $e) {
			// Tagging is a UX nicety, never let it break the transcription flow.
			$this->logger->warning('TranscriptionTagService: failed to mark file ' . $fileId . ' in progress: ' . $e->getMessage());
		}

		// A retry after a previous failure should no longer show as failed.
		$this->clearFailed($fileId);
	}

	public function clearInProgress(int $fileId): void {
		try {
			$tag = $this->getInProgressTag();
			$this->tagObjectMapper->unassignTags((string)$fileId, self::OBJECT_TYPE, $tag->getId());
		} catch (\Exception $e) {
			$this->logger->warning('TranscriptionTagService: failed to clear in-progress tag on file ' . $fileId . ': ' . $e->getMessage());
		}
	}

	public function markFailed(int $fileId): void {
		try {
			$tag = $this->getFailedTag();
			$this->tagObjectMapper->assignTags((string)$fileId, self::OBJECT_TYPE, $tag->getId());
		} catch (\Exception $e) {
			$this->logger->warning('TranscriptionTagService: failed to mark file ' . $fileId . ' failed: ' . $e->getMessage());
		}
	}

	public function clearFailed(int $fileId): void {
		try {
			$tag = $this->getFailedTag();
			$this->tagObjectMapper->unassignTags((string)$fileId, self::OBJECT_TYPE, $tag->getId());
		} catch (\Exception $e) {
			$this->logger->warning('TranscriptionTagService: failed to clear failed tag on file ' . $fileId . ': ' . $e->getMessage());
		}
	}
}
