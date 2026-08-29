// Ported from linto-studio/studio-api components/EditorHandler/utils/countSyllabsFromWord.js
// (CommonJS -> ESM only, logic untouched).

export function countSyllabsFromWord(word, syllabic) {
  return syllabic.count(word) || 1
}
