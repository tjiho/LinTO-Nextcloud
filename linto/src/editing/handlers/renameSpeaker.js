import { findSpeaker } from "../tools/findSpeaker"
import { normalizeWhitespace } from "../tools/normalizeWhitespace"

export async function renameSpeaker(doc, persist, core, { translationId, speakerId, name }) {
  const speaker = findSpeaker(doc, speakerId)
  if (!speaker) return { ok: false, reason: "unknown_speaker" }
  speaker.speaker_name = normalizeWhitespace(name)

  const persisted = await persist()
  if (!persisted.ok) return persisted

  core.transcriptionEditor?.applySpeakerRenamed({
    translationId,
    speakerId,
    name: speaker.speaker_name,
  })
  return { ok: true }
}
