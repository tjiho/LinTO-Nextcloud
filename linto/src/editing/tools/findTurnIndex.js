export function findTurnIndex(doc, turnId) {
  return (doc.text || []).findIndex((t) => t.turn_id === turnId)
}
