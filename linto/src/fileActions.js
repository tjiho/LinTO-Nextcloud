// src/actions/openModalAction.js

import { registerFileAction, DefaultType } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'

const TRANSCRIPT_MIMETYPE = 'application/vnd.linto.transcript+zip'

// Same artwork as img/app.svg: a single evenodd path so the eyes and mouth
// stay punched out, and currentColor so the action picks up the list's text
// colour in both themes like every other file action.
const lintoSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M 19.661 19.888 c 0 -0.011 0 -0.011 0 0, 2.074 -1.989 3.333 -4.804 3.333 -7.894 C 22.994 5.92 18.074 1 12 1, 5.926 1 1.005 5.92 1.005 11.994 c 0 6.074 4.921 10.995 10.995 10.995, 0.116 0 0.243 0 0.36 -0.011 l 8.645 0.021 c -0.931 -0.624 -1.778 -1.767 -1.344 -3.111 z M 9.873 11.529 c 0 0.624 -0.508 1.122 -1.122 1.122, -0.624 0 -1.122 -0.508 -1.122 -1.122, 0 -0.624 0.508 -1.122 1.122 -1.122, 0.614 -0.011 1.122 0.497 1.122 1.122 z M 16.381 11.529 c 0 0.624 -0.508 1.122 -1.122 1.122, -0.624 0 -1.122 -0.508 -1.122 -1.122, 0 -0.624 0.508 -1.122 1.122 -1.122, 0.614 -0.011 1.122 0.497 1.122 1.122 z M 11.936 16.904 c -0.677 0 -1.333 -0.095 -1.757 -0.317, -0.317 -0.169 -0.444 -0.55 -0.286 -0.857, 0.169 -0.317 0.55 -0.444 0.857 -0.286, 0.455 0.233 1.958 0.222 2.487 -0.011, 0.317 -0.148 0.698 0 0.847 0.317, 0.148 0.317 0 0.698 -0.317 0.847, -0.413 0.201 -1.111 0.307 -1.831 0.307 z"/></svg>'

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
