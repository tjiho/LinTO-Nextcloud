// src/actions/openModalAction.js

import { registerFileAction } from '@nextcloud/files'


function addAction() {
  console.log("File action loaded")

  const action = {
    id: 'linto-transcribe',
    displayName: () => 'Transcrire avec LinTO',
    iconSvgInline() {
      return '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 512.008 512.008" style="enable-background:new 0 0 512.008 512.008;" xml:space="preserve"> <g> <g> <path d="M451.291,436.781H85.96c-23.704,0-42.98-19.277-42.98-42.98c0-23.703,19.277-42.98,42.98-42.98h42.98 c11.862,0,21.49-9.628,21.49-21.49s-9.628-21.49-21.49-21.49H85.96c-47.407,0-85.96,38.553-85.96,85.96s38.553,85.96,85.96,85.96 h365.331c11.862,0,21.49-9.628,21.49-21.49S463.153,436.781,451.291,436.781z"/> </g> </g> <g> <g> <path d="M487.996,56.278c-32.063-32.02-84.241-32.063-116.369,0l-32.88,32.88c-0.408,0.344-0.903,0.473-1.289,0.86 s-0.516,0.881-0.86,1.289L226.333,201.572c-21.232,21.275-32.923,49.556-32.923,79.621v48.138c0,11.862,9.628,21.49,21.49,21.49 h48.138c30.065,0,58.345-11.691,79.664-32.944l145.273-145.273C520.016,140.541,520.016,88.341,487.996,56.278z M312.336,287.468 c-13.173,13.13-30.688,20.373-49.298,20.373H236.39v-26.648c0-18.61,7.242-36.125,20.351-49.277l96.125-96.125l55.573,55.573 L312.336,287.468z M457.587,142.217l-18.761,18.761l-55.573-55.573l18.739-18.739c15.322-15.301,40.272-15.279,55.573,0 C472.867,101.988,472.867,126.916,457.587,142.217z"/> </g> </g> </svg>';
    },
    order: 1,
    exec: async ({ nodes }) => {
      transcribe(nodes[0])
    },
    enabled: () => true,
  }

  registerFileAction(action)

  // Action pour visualiser les fichiers .transcript
  // const viewAction = {
  //   id: 'linto-view-transcript',
  //   displayName: () => 'Voir la transcription',
  //   iconSvgInline: () => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" fill="currentColor"/><path d="M14 2v6h6M12 18v-6M9 15h6M9 18H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
  //   order: 2,
  //   enabled: (nodes) => {
  //     return nodes.length === 1 && nodes[0].name.endsWith('.transcript');
  //   },
  //   exec: ({ nodes }) => {
  //     const fileId = nodes[0].fileId;
  //     const url = OC.generateUrl(`apps/linto/view/${fileId}`);
  //     window.open(url, '_blank');
  //   }
  // };
  //
  const viewAction = {
    id: 'linto-view',
    displayName: () => 'Ouvrir avec LinTO',
    iconSvgInline() {
      return '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 512.008 512.008" style="enable-background:new 0 0 512.008 512.008;" xml:space="preserve"> <g> <g> <path d="M451.291,436.781H85.96c-23.704,0-42.98-19.277-42.98-42.98c0-23.703,19.277-42.98,42.98-42.98h42.98 c11.862,0,21.49-9.628,21.49-21.49s-9.628-21.49-21.49-21.49H85.96c-47.407,0-85.96,38.553-85.96,85.96s38.553,85.96,85.96,85.96 h365.331c11.862,0,21.49-9.628,21.49-21.49S463.153,436.781,451.291,436.781z"/> </g> </g> <g> <g> <path d="M487.996,56.278c-32.063-32.02-84.241-32.063-116.369,0l-32.88,32.88c-0.408,0.344-0.903,0.473-1.289,0.86 s-0.516,0.881-0.86,1.289L226.333,201.572c-21.232,21.275-32.923,49.556-32.923,79.621v48.138c0,11.862,9.628,21.49,21.49,21.49 h48.138c30.065,0,58.345-11.691,79.664-32.944l145.273-145.273C520.016,140.541,520.016,88.341,487.996,56.278z M312.336,287.468 c-13.173,13.13-30.688,20.373-49.298,20.373H236.39v-26.648c0-18.61,7.242-36.125,20.351-49.277l96.125-96.125l55.573,55.573 L312.336,287.468z M457.587,142.217l-18.761,18.761l-55.573-55.573l18.739-18.739c15.322-15.301,40.272-15.279,55.573,0 C472.867,101.988,472.867,126.916,457.587,142.217z"/> </g> </g> </svg>';
    },
    order: 2,
    exec: async ({ nodes }) => {
      const file = nodes[0]
      const fileId = file.id ?? file.fileId
      const url = OC.generateUrl(`apps/linto/view/${fileId}`)
      window.open(url, '_blank')
    },
    enabled: () => true,
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

  console.log(response)
}

addAction()
