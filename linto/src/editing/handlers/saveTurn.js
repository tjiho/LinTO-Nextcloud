import { computeRetimedTurn } from "../../retiming/index.js"
import { findTurnIndex } from "../tools/findTurnIndex"
import { normalizeWhitespace } from "../tools/normalizeWhitespace"
import { apiWordsToWireWords } from "../tools/apiWordsToWireWords.js"

export async function saveTurn(doc, persist, core, { translationId, turnId, text }) {
  const turnIndex = findTurnIndex(doc, turnId)
  if (turnIndex === -1) return { ok: false, reason: "conflict" }

  const normalized = normalizeWhitespace(text)
  const retimed = computeRetimedTurn(doc.text[turnIndex], normalized)
  doc.text[turnIndex] = { ...doc.text[turnIndex], ...retimed }

  const persisted = await persist()
  if (!persisted.ok) return persisted

  core.transcriptionEditor?.applyTurnUpdate({
    translationId,
    turnId,
    text: doc.text[turnIndex].segment,
    words: apiWordsToWireWords(doc.text[turnIndex].words),
    stime: doc.text[turnIndex].stime,
    etime: doc.text[turnIndex].etime,
  })
  return { ok: true }
}
