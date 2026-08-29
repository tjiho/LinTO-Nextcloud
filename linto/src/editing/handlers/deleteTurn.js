import { findTurnIndex } from "../tools/findTurnIndex"
import { removeSpeakerIfUnused } from "../tools/removeSpeakerIfUnused"

export async function deleteTurn(doc, persist, core, { translationId, turnId }) {
  const turnIndex = findTurnIndex(doc, turnId)
  if (turnIndex === -1) return { ok: false, reason: "conflict" }
  if (doc.text.length <= 1) return { ok: false, reason: "last_turn" }

  const [removed] = doc.text.splice(turnIndex, 1)
  const removedSpeakerId = removeSpeakerIfUnused(doc, removed.speaker_id)

  const persisted = await persist()
  if (!persisted.ok) return persisted

  core.transcriptionEditor?.applyTurnDeleted({ translationId, turnId, removedSpeakerId })
  return { ok: true }
}
