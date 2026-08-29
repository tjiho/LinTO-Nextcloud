// Ported from linto-studio/studio-api components/EditorHandler/utils/toWireTurn.js
// (CommonJS -> ESM only, logic untouched): converts a turn from the
// stored/retiming shape (turn_id, segment, speaker_id, words with wid) to
// WireTurn, the shape @linto-ai/transcript-ui-plugin-transcription-editor's
// apply* handlers expect (turnId, text, words without wid, speakerId).
import { apiWordsToWireWords } from "./apiWordsToWireWords"

export function apiTurnToWireTurn(turn) {
  return {
    turnId: turn.turn_id,
    text: turn.segment,
    words: apiWordsToWireWords(turn.words),
    stime: turn.stime,
    etime: turn.etime,
    speakerId: turn.speaker_id ?? null,
    language: turn.language || "",
  }
}
