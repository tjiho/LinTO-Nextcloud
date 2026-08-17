import { createAppConfig } from '@nextcloud/vite-config'
import { join, resolve } from 'path'

export default createAppConfig(
  {
    main: resolve(join('src', 'main.js')),
    fileActions: resolve(join('src', 'fileActions.js')),
    settings: resolve(join('src', 'settings.js')),
    viewer: resolve(join('src', 'viewer.js')),
  },
  {
    createEmptyCSSEntryPoints: true,
    extractLicenseInformation: true,
    thirdPartyLicense: false,
    // config: {
    //   build: {
    //     cssCodeSplit: false,
    //     rollupOptions: {
    //       external: [
    //         'vite-plugin-node-polyfills/shims/global',
    //         'vite-plugin-node-polyfills/shims/buffer',
    //         'vite-plugin-node-polyfills/shims/process',
    //       ],
    //     },
    //   },
    // },
  },
)
