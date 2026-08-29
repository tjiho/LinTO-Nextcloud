import { computeSplitTurns } from "../../retiming/index.js"
import { findTurnIndex } from "../tools/findTurnIndex"
import { apiTurnToWireTurn } from "../tools/apiTurnToWireTurn.js"

export async function splitTurn(doc, persist, core, { translationId, turnId, offset }) {
  const turnIndex = findTurnIndex(doc, turnId)
  if (turnIndex === -1) return { ok: false, reason: "conflict" }

  const split = computeSplitTurns(doc.text[turnIndex], offset)
  if (!split) return { ok: false, reason: "invalid_offset" }
  doc.text.splice(turnIndex, 1, split.left, split.right)

  const persisted = await persist()
  if (!persisted.ok) return persisted

  core.transcriptionEditor?.applyTurnSplit({
    translationId,
    originalTurnId: turnId,
    turns: [apiTurnToWireTurn(split.left), apiTurnToWireTurn(split.right)],
  })
  return { ok: true }
}
