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


const loading = ref(true)
const editorRef = useTemplateRef<InstanceType<typeof TranscriptUI>>("editor")
let core!: Core





onMounted(() => {
  core = editorRef.value!.core
  const content:any = loadState('linto', 'content')
  const mode = 'view' // this.canWrite ? "edit" : "view"


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
  core.capabilities.value = { text: mode, speakers: mode }

  const transcript = JSON.parse(content.transcript)
  transcript.name = content.fileName
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
