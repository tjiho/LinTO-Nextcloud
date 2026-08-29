export function findSpeaker(doc, speakerId) {
  return (doc.speakers || []).find((s) => s.speaker_id === speakerId)
}
