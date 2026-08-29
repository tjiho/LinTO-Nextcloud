// Ported from linto-studio/studio-api components/EditorHandler/utils/computeMergedTurn.js
// (CommonJS -> ESM only, logic untouched).

import { computeWordLayout } from "./computeWordLayout"
import { firstDefinedTime, lastDefinedTime, assignTurnTimes } from "./turnTimes"

function deriveText(turn) {
  const laid = computeWordLayout(turn.words)
  if (laid.length > 0) return laid.map((w) => w.text).join(" ")
  return (turn.segment || "").replace(/\s+/g, " ").trim()
}

/**
 * Merge two adjacent turns: texts/words concatenate, times are outer bounds.
 * The larger turn (derived-text length, first wins ties) provides id, speaker
 * and every other attribute.
 */
export function computeMergedTurn(firstTurn, secondTurn) {
  const firstText = deriveText(firstTurn)
  const secondText = deriveText(secondTurn)
  const larger = secondText.length > firstText.length ? secondTurn : firstTurn

  const segment = [firstText, secondText].filter((t) => t !== "").join(" ")
  const words = [...(firstTurn.words || []), ...(secondTurn.words || [])]

  const merged = { ...larger, segment, raw_segment: segment, words }
  assignTurnTimes(
    merged,
    firstDefinedTime(words, "stime") ?? firstTurn.stime ?? secondTurn.stime,
    lastDefinedTime(words, "etime") ?? secondTurn.etime ?? firstTurn.etime,
  )
  return merged
}
