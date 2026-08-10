import { registerFileAction } from '@nextcloud/files'

registerFileAction({
  id: 'linto-transcribe',
  displayName: () => 'Transcrire avec LinTO',
  enabled(nodes) {
    return nodes.every((node) => node.mime.startsWith('audio/'))
  },
  async exec({ nodes, view, folder, contents }) {
    return
    // adapter la signature : file → nodes (tableau), dir → folder
  },
})


 //  registerFileAction({
	// id: 'linto-transcribe',
	// displayName: () => 'Transcrire avec LinTO',
	// iconSvgInline: () => '<svg>...</svg>', // ton icône SVG

	// // Restreint l'action aux fichiers mp3
	// enabled(nodes) {
	// 	return nodes.every((node) => node.mime.startsWith('audio/'))
	// },

	// async exec(node) {
	// 	const url = OC.generateUrl('apps/linto/transcribe')
	// 	const response = await fetch(url, {
	// 		method: 'POST',
	// 		headers: {
	// 			'Content-Type': 'application/json',
	// 			requesttoken: OC.requestToken
	// 		},
	// 		body: JSON.stringify({
	// 			fileId: node.fileid
	// 		})
	// 	})
	// 	return response.ok
	// },

	// order: 10,
 //  })
