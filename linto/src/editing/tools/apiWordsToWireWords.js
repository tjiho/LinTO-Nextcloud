// Ported from linto-studio/studio-api components/EditorHandler/utils/toWireWords.js
// (CommonJS -> ESM only, logic untouched). No wid on the wire: the plugin
// consumes words positionally (token index).
export function apiWordsToWireWords(words) {
  return (words || []).map(({ wid, ...word }) => word)
}
