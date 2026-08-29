import { findTurnIndex } from "../tools/findTurnIndex"
import { removeSpeakerIfUnused } from "../tools/removeSpeakerIfUnused"
import { resolveOrCreateSpeaker } from "../tools/resolveOrCreateSpeaker"

export async function updateTurnSpeaker(
  doc,
  persist,
  core,
  { translationId, turnId, speakerId, speakerName },
) {
  const turnIndex = findTurnIndex(doc, turnId)
  if (turnIndex === -1) return { ok: false, reason: "conflict" }

  const speaker = resolveOrCreateSpeaker(doc, speakerId, speakerName)
  if (!speaker) return { ok: false, reason: "unknown_speaker" }

  const previous = doc.text[turnIndex].speaker_id
  if (previous === speaker.speaker_id) return { ok: true }
  doc.text[turnIndex] = { ...doc.text[turnIndex], speaker_id: speaker.speaker_id }
  const removedSpeakerId = removeSpeakerIfUnused(doc, previous)

  const persisted = await persist()
  if (!persisted.ok) return persisted

  core.transcriptionEditor?.applyTurnSpeakerUpdated({
    translationId,
    turnId,
    speaker: { id: speaker.speaker_id, name: speaker.speaker_name },
    removedSpeakerId,
  })
  return { ok: true }
}
