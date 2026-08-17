import { createApp } from 'vue'
import Viewer from './Viewer.vue'
import { register as registerLintoEditor } from "@linto/transcript-ui/webcomponent"
const app = createApp(Viewer)
app.mount('#linto-viewer')
registerLintoEditor()
