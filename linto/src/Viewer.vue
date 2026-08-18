<script setup lang="ts">
import { loadState } from '@nextcloud/initial-state'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcContent from '@nextcloud/vue/components/NcContent'
import { createAudioPlugin, mapApiDocument } from '@linto/transcript-ui/webcomponent'
import { ref, onMounted } from 'vue'
import { generateUrl } from '@nextcloud/router'
const editor = ref()

onMounted(() => {
  setTimeout(() => {
    const content = loadState('linto', 'content')
    const core = editor.value.core
    const mode = 'view' // this.canWrite ? "edit" : "view"
    const transcript = JSON.parse(content.transcript)
    transcript.name = content.fileName

    core.use(
      createAudioPlugin({
        resolveSrc: async (source) => {
          const response = await fetch(generateUrl(`apps/linto/api/audio/${content.fileId}`))
          if (!response.ok) throw new Error('Audio unavailable')
          const blob = await response.blob()
          return URL.createObjectURL(blob)
        }
      })
    )
    core.capabilities.value = { text: mode, speakers: mode }
    core.setDocument(mapApiDocument(transcript))
  }, 500)
})

</script>

<template>
	<NcContent app-name="linto">
		<NcAppContent :class="$style.content">
		    <linto-editor ref="editor" locale="FR-fr" :class="$style.editor"/>
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
