/** Drop a speaker from doc.speakers once no turn references it any more;
 *  returns its id (for the GC-consequence field on the caller's result) or
 *  undefined when the speaker is still in use. */
export function removeSpeakerIfUnused(doc, speakerId) {
  if (!speakerId) return undefined
  const stillUsed = doc.text.some((t) => t.speaker_id === speakerId)
  if (stillUsed) return undefined
  doc.speakers = (doc.speakers || []).filter((s) => s.speaker_id !== speakerId)
  return speakerId
}
