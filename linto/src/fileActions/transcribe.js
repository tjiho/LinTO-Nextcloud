  import { registerFileAction, FileAction } from '@nextcloud/files'

  registerFileAction(new FileAction({
	id: 'linto-transcribe',
	displayName: () => 'Transcrire avec LinTO',
	iconSvgInline: () => '<svg>...</svg>', // ton icône SVG

	// Restreint l'action aux fichiers mp3
	enabled(nodes) {
		return nodes.every((node) => node.mime.startsWith('audio/'))
	},

	async exec(node) {
		const url = OC.generateUrl('apps/linto/transcribe')
		const response = await fetch(url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				requesttoken: OC.requestToken
			},
			body: JSON.stringify({
				fileId: node.fileid
			})
		})
		return response.ok
	},

	order: 10,
  }))
