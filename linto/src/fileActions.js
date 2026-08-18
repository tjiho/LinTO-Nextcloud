// src/actions/openModalAction.js

import { registerFileAction, DefaultType } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'

const TRANSCRIPT_MIMETYPE = 'application/vnd.linto.transcript+zip'

const lintoSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="300" height="300"><g transform="matrix(1.4358874,0,0,1.4358874,-65.37151,-15.728904)"><path fill="#4BB9EA" d="m 222.4,190 c 0,-0.1 0,-0.1 0,0 19.6,-18.8 31.5,-45.4 31.5,-74.6 C 253.9,58 207.4,11.5 150,11.5 92.6,11.5 46.1,58 46.1,115.4 c 0,57.4 46.5,103.9 103.9,103.9 1.1,0 2.3,0 3.4,-0.1 l 81.7,0.2 c -8.8,-5.9 -16.8,-16.7 -12.7,-29.4 z"/><path fill="#020202" d="m 129.9,111 c 0,5.9 -4.8,10.6 -10.6,10.6 -5.9,0 -10.6,-4.8 -10.6,-10.6 0,-5.9 4.8,-10.6 10.6,-10.6 5.8,-0.1 10.6,4.7 10.6,10.6 z"/><path fill="#020202" d="m 191.4,111 c 0,5.9 -4.8,10.6 -10.6,10.6 -5.9,0 -10.6,-4.8 -10.6,-10.6 0,-5.9 4.8,-10.6 10.6,-10.6 5.8,-0.1 10.6,4.7 10.6,10.6 z"/><path fill="#020202" d="m 149.4,161.8 c -6.4,0 -12.6,-0.9 -16.6,-3 -3,-1.6 -4.2,-5.2 -2.7,-8.1 1.6,-3 5.2,-4.2 8.1,-2.7 4.3,2.2 18.5,2.1 23.5,-0.1 3,-1.4 6.6,0 8,3 1.4,3 0,6.6 -3,8 -3.9,1.9 -10.5,2.9 -17.3,2.9 z"/></g></svg>';

function addAction() {
  const action = {
    id: 'linto-transcribe',
    displayName: () => t('linto', 'Transcribe with LinTO'),
    iconSvgInline() {
      return lintoSvg
    },
    order: 1,
    exec: async ({ nodes }) => {
      transcribe(nodes[0])
    },
    enabled: ({ nodes }) => {
      if (nodes.length === 1) {
        if (nodes[0].mime.startsWith('audio/')) {
          return true
        }
      }
      return false
    },
  }

  registerFileAction(action)

  const viewAction = {
    id: 'linto-view',
    displayName: () => t('linto', 'Open with LinTO'),
    iconSvgInline() {
      return lintoSvg
    },
    order: 2,
    default: DefaultType.DEFAULT,
    exec: async ({ nodes }) => {
      const file = nodes[0]
      const fileId = file.id ?? file.fileId
      const url = OC.generateUrl(`apps/linto/view/${fileId}`)
      window.open(url, '_blank')
    },
    enabled: ({ nodes }) => {
      if (nodes.length === 1) {
        if (nodes[0].mime === TRANSCRIPT_MIMETYPE) {
          return true
        }
      }
      return false
    },
  }

  registerFileAction(viewAction)
}

async function transcribe(node) {
  const url = OC.generateUrl('apps/linto/transcribe')
  const data = {
    fileId: node.id ?? node.fileId
  }
  const response = await fetch(url,
    {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        requesttoken: OC.requestToken,
        'Content-Type': 'application/json',
      }
    }
  )

  if (response.status === 409) {
    OC.Notification.showTemporary(t('linto', 'A transcription is already in progress for this file'), { type: 'error' })
    return
  }

  if (!response.ok) {
    OC.Notification.showTemporary(t('linto', 'Failed to start transcription'), { type: 'error' })
    return
  }

  console.debug(response)
}

addAction()
