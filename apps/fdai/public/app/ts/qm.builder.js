"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
Object.defineProperty(exports, "__esModule", { value: true });
// Usage:
// npm install typescript ts-node
// npx ts-node ts/gi-run.ts
// process.env.RELEASE_STAGE = "ionic"
var env_helper_1 = require("./env-helper");
(0, env_helper_1.loadEnvFromDopplerOrDotEnv)(".env");
var qmLog = __importStar(require("./qm.log"));
qmLog.info("Building for: " + (0, env_helper_1.getQMClientIdOrException)());
var qm_app_settings_1 = require("./qm.app-settings");
var qm_build_info_helper_1 = require("./qm.build-info-helper");
(0, qm_build_info_helper_1.writeBuildInfoFile)().then(function () {
    (0, qm_app_settings_1.saveAppSettings)();
});
//# sourceMappingURL=qm.builder.js.map