// Usage:
// npm install typescript ts-node
// npx ts-node ts/gi-runner-ionic.ts
import * as gi from "./gi-functions"
gi.runIonicFailedAll(function() {
    process.exit(0)
})
