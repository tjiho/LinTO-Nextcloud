// Ported from linto-studio/studio-api components/EditorHandler/utils/syllabic/syllabicEN.js
// (CommonJS -> ESM only, logic untouched).

import { syllable } from "syllable"
import { Syllabic } from "./syllabic"

export class SyllabicEN extends Syllabic {
  constructor(language = "en-US") {
    super(language)
  }

  count(term) {
    return syllable(term)
  }
}
