<script setup lang="ts">
import { loadState } from '@nextcloud/initial-state'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcContent from '@nextcloud/vue/components/NcContent'
import { generateUrl } from '@nextcloud/router'

import { ref, onMounted, useTemplateRef } from 'vue'


import {
  TranscriptUI,
  mapApiDocument,
  mapWhisperXDocument,
  createCore,
  provideCore,
  type Core,
} from "@linto-ai/transcript-ui-core"
import { provideI18n, type Locale } from "@linto-ai/transcript-ui-i18n"
import { createAudioPlugin } from "@linto-ai/transcript-ui-plugin-audio"
import { createTranscriptionEditorPlugin } from "@linto-ai/transcript-ui-plugin-transcription-editor"
//import { createLLMServicesPlugin } from "@linto-ai/transcript-ui-plugin-llm-services"

import { saveTranscript } from "./editing/saveTranscript.js"
import { saveTurn } from "./editing/handlers/saveTurn.js"
import { splitTurn } from "./editing/handlers/splitTurn.js"
import { mergeTurns } from "./editing/handlers/mergeTurns.js"
import { deleteTurn } from "./editing/handlers/deleteTurn.js"
import { updateTurnSpeaker } from "./editing/handlers/updateTurnSpeaker.js"
import { renameSpeaker } from "./editing/handlers/renameSpeaker.js"
import { replaceSpeaker } from "./editing/handlers/replaceSpeaker.js"

import './nextcloud-dialog-reset.css'

const loading = ref(true)
const editorRef = useTemplateRef<InstanceType<typeof TranscriptUI>>("editor")
let core!: Core

onMounted(() => {
  core = editorRef.value!.core
  const content: any = loadState('linto', 'content')
  const mode = content.readOnly ? "view" : "edit"

  core.use(
    createAudioPlugin({
      resolveSrc: async () => {
        const response = await fetch(generateUrl(`apps/linto/api/audio/${content.fileId}`))
        if (!response.ok) throw new Error('Audio unavailable')
        const blob = await response.blob()
        return URL.createObjectURL(blob)
      }
    })
  )

  //core.use(createLLMServicesPlugin())
  core.capabilities.value = { text: mode, speakers: mode }

  const transcript = JSON.parse(content.transcript)
  transcript.name = content.fileName

  // No lockTurn/unlockTurn/refetchTranslation: the plugin runs "local-only"
  // without them (single editor, no realtime broadcast — see
  // src/editing/handlers/).
  if (mode === "edit") {
    const doc = transcript
    const persist = () => saveTranscript(content.fileId, doc)
    core.use(
      createTranscriptionEditorPlugin({
        saveTurn: (payload) => saveTurn(doc, persist, core, payload),
        splitTurn: (payload) => splitTurn(doc, persist, core, payload),
        mergeTurns: (payload) => mergeTurns(doc, persist, core, payload),
        deleteTurn: (payload) => deleteTurn(doc, persist, core, payload),
        updateTurnSpeaker: (payload) => updateTurnSpeaker(doc, persist, core, payload),
        renameSpeaker: (payload) => renameSpeaker(doc, persist, core, payload),
        replaceSpeaker: (payload) => replaceSpeaker(doc, persist, core, payload),
      }),
    )
  }

  core.setDocument(mapApiDocument(transcript))
  loading.value = false
})

</script>

<template>
	<NcContent app-name="linto">
		<NcAppContent :class="$style.content">
		   <TranscriptUI ref="editor" locale="fr" :class="$style.editor"/>
		</NcAppContent>
	</NcContent>
</template>

<style module>
.content {
	display: flex;
	justify-content: center;
	margin: 16px;
}

.editor {
    width: 100%;
}
</style>
