import { computeMergedTurn } from "../../retiming/index.js"
import { findTurnIndex } from "../tools/findTurnIndex"
import { apiTurnToWireTurn } from "../tools/apiTurnToWireTurn.js"

export async function mergeTurns(doc, persist, core, { translationId, firstTurnId, secondTurnId }) {
  const turnIndex = findTurnIndex(doc, firstTurnId)
  if (turnIndex === -1) return { ok: false, reason: "conflict" }
  const first = doc.text[turnIndex]
  const second = doc.text[turnIndex + 1]
  if (!second || second.turn_id !== secondTurnId) {
    return { ok: false, reason: "not_adjacent" }
  }

  const merged = computeMergedTurn(first, second)
  doc.text.splice(turnIndex, 2, merged)
  const removedTurnId = merged.turn_id === firstTurnId ? secondTurnId : firstTurnId

  const persisted = await persist()
  if (!persisted.ok) return persisted

  core.transcriptionEditor?.applyTurnsMerged({
    translationId,
    mergedTurnId: merged.turn_id,
    removedTurnId,
    turn: apiTurnToWireTurn(merged),
  })
  return { ok: true }
}
