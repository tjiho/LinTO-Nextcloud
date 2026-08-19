<script setup lang="ts">
import { ref } from 'vue'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import { translate as t } from '@nextcloud/l10n'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import NcPasswordField from '@nextcloud/vue/components/NcPasswordField'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'

interface SettingsState {
	apiUrl: string
	organisationId: string
	apiKey: string
	deleteRemoteAfterTranscription: string
}

const initial = loadState<SettingsState>('linto', 'settings', {
	apiUrl: '',
	organisationId: '',
	apiKey: '',
	deleteRemoteAfterTranscription: '1',
})

const apiUrl = ref(initial.apiUrl)
const organisationId = ref(initial.organisationId)
const apiKey = ref(initial.apiKey)
const deleteRemoteAfterTranscription = ref(initial.deleteRemoteAfterTranscription === '1')

const saving = ref(false)
const feedback = ref<{ type: 'success' | 'error'; message: string } | null>(null)

async function save() {
	saving.value = true
	feedback.value = null

	try {
		const response = await fetch(generateUrl('apps/linto/config'), {
			method: 'POST',
			body: JSON.stringify({
				values: {
					apiUrl: apiUrl.value,
					organisationId: organisationId.value,
					apiKey: apiKey.value,
					deleteRemoteAfterTranscription: deleteRemoteAfterTranscription.value ? '1' : '0',
				},
			}),
			headers: {
				requesttoken: OC.requestToken,
				'Content-Type': 'application/json',
			},
		})

		if (!response.ok) {
			throw new Error(`HTTP ${response.status}`)
		}

		feedback.value = { type: 'success', message: t('linto', 'Settings saved') }
	} catch (e) {
		feedback.value = { type: 'error', message: t('linto', 'Failed to save settings') }
	} finally {
		saving.value = false
	}
}
</script>

<template>
	<NcSettingsSection
		name="LinTO"
		:description="t('linto', 'Configure the transcription settings.')">
		<form :class="$style.lintoForm" @submit.prevent="save">
			<NcTextField
				v-model="apiUrl"
				:label="t('linto', 'LinTO API URL')"
				placeholder="https://studio.linto.ai" />

			<NcTextField
				v-model="organisationId"
				:label="t('linto', 'Organisation ID')"
				:placeholder="t('linto', 'Your LinTO organisation ID')" />

			<NcPasswordField
				v-model="apiKey"
				:label="t('linto', 'API Key')"
				:placeholder="t('linto', 'Your LinTO API key')" />

			<NcCheckboxRadioSwitch v-model="deleteRemoteAfterTranscription" type="switch">
				{{ t('linto', 'Delete the transcription from LinTO Studio once finished, keep only the local copy') }}
			</NcCheckboxRadioSwitch>

			<NcButton type="submit" variant="primary" :disabled="saving">
				{{ t('linto', 'Save') }}
			</NcButton>

			<NcNoteCard v-if="feedback" :type="feedback.type" :text="feedback.message" />
		</form>
	</NcSettingsSection>
</template>

<style module>
.lintoForm {
	display: flex;
	flex-direction: column;
	gap: 16px;
	max-width: 400px;
}
</style>
