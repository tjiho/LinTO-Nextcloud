// Ported from linto-studio/studio-api components/EditorHandler/utils/syllabic/index.js
// (CommonJS -> ESM only, logic untouched).

import { SyllabicFR } from "./syllabicFR"
import { SyllabicEN } from "./syllabicEN"

const cache = new Map()

export function getSyllabic(language) {
  const code = (language || "").toLowerCase()
  const family = code.split(/[-_]/)[0]

  if (cache.has(family)) return cache.get(family)

  let instance
  switch (family) {
    case "en":
      instance = new SyllabicEN()
      break
    case "fr":
    default:
      instance = new SyllabicFR()
      break
  }
  cache.set(family, instance)
  return instance
}
