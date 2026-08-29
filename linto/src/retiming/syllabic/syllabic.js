// Ported from linto-studio/studio-api components/EditorHandler/utils/syllabic/syllabic.js
// (CommonJS -> ESM only, logic untouched).

export class Syllabic {
  constructor(language) {
    this.language = language
  }

  count(term) {
    throw new Error("Not implemented")
  }

  syllabify(term) {
    throw new Error("Not implemented")
  }
}
