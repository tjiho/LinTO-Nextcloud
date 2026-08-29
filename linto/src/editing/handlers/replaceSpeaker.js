import { findSpeaker } from "../tools/findSpeaker"

export async function replaceSpeaker(doc, persist, core, { translationId, fromSpeakerId, toSpeakerId }) {
  if (!findSpeaker(doc, fromSpeakerId) || !findSpeaker(doc, toSpeakerId)) {
    return { ok: false, reason: "unknown_speaker" }
  }
  doc.text = doc.text.map((t) =>
    t.speaker_id === fromSpeakerId ? { ...t, speaker_id: toSpeakerId } : t,
  )
  doc.speakers = doc.speakers.filter((s) => s.speaker_id !== fromSpeakerId)

  const persisted = await persist()
  if (!persisted.ok) return persisted

  core.transcriptionEditor?.applySpeakerReplaced({ translationId, fromSpeakerId, toSpeakerId })
  return { ok: true }
}
