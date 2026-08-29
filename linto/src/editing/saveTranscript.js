import { generateUrl } from "@nextcloud/router"

/** PUT the whole edited document back — TranscriptController::saveTranscript
 *  swaps transcript.json inside the .transcript ZIP, keeping audio.mp3 and
 *  metadata.json untouched. */
export async function saveTranscript(fileId, document) {
  const response = await fetch(generateUrl(`apps/linto/api/transcript/${fileId}`), {
    method: "PUT",
    body: JSON.stringify({ document }),
    headers: {
      requesttoken: OC.requestToken,
      "Content-Type": "application/json",
    },
  })
  if (!response.ok) {
    return { ok: false, reason: `http_${response.status}` }
  }
  return { ok: true }
}
