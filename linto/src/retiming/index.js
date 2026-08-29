// Public surface — ported from linto-studio/studio-api's EditorHandler,
// the same algorithm the collaborative editor uses server-side. Kept here
// as pure functions with no backend/lock/Mongo coupling: this app persists
// a single edited turn straight back into the .transcript ZIP, no
// concurrency to arbitrate (single editor, no realtime broadcast).

export { computeRetimedTurn } from "./computeRetimedTurn"
export { computeSplitTurns } from "./computeSplitTurns"
export { computeMergedTurn } from "./computeMergedTurn"
