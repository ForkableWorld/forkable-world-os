// Usage:
// npm install typescript ts-node
// npx ts-node ts/gi-run.ts
// process.env.RELEASE_STAGE = "ionic"
import {getQMClientIdOrException, loadEnvFromDopplerOrDotEnv} from "./env-helper"
loadEnvFromDopplerOrDotEnv(".env")
import * as qmLog from "./qm.log"
qmLog.info("Building for: "+ getQMClientIdOrException())
import {saveAppSettings} from "./qm.app-settings"
import {writeBuildInfoFile} from "./qm.build-info-helper"
writeBuildInfoFile().then(() => {
    saveAppSettings()
})
