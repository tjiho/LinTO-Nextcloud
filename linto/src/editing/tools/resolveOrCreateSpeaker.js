import { findSpeaker } from "./findSpeaker"
import { generateId } from "../../tools/generateId"

// Exactly one of speakerId / speakerName, same contract as the plugin.
export function resolveOrCreateSpeaker(doc, speakerId, speakerName) {
  if (speakerId) {
    const existing = findSpeaker(doc, speakerId)
    return existing
      ? { speaker_id: existing.speaker_id, speaker_name: existing.speaker_name }
      : null
  }
  const speaker = { speaker_id: generateId(), speaker_name: (speakerName || "").trim() }
  doc.speakers = [...(doc.speakers || []), speaker]
  return speaker
}
