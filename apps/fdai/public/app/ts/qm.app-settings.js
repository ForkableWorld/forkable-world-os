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
exports.writeAppSettingsToFile = exports.saveAppSettings = void 0;
// import * as api from "../typescript-node-client/api"
var env = __importStar(require("./env-helper"));
var fileHelper = __importStar(require("./qm.file-helper"));
var qmLog = __importStar(require("./qm.log"));
var timeHelper = __importStar(require("./qm.time-helper"));
var testHelpers = __importStar(require("./test-helpers"));
// tslint:disable-next-line:no-var-requires
var qm = require("../public/js/qmHelpers.js");
env.loadEnvFromDopplerOrDotEnv(".env");
function isTruthy(value) { return (value && value !== "false"); }
function getRequestOptions(path) {
    var options = {
        headers: { "User-Agent": "Request-Promise", "Content-Type": "application/json" },
        json: true,
        qs: {
            access_token: env.getAccessToken(),
            allStaticAppData: true,
            clientId: env.getQMClientIdOrException(),
            includeClientSecret: true,
        },
        uri: qm.getAppHostName() + path,
    };
    if (options.qs.access_token) {
        qmLog.info("Using CUREDAO_PERSONAL_ACCESS_TOKEN: " + options.qs.access_token.substring(0, 4) + "...");
    }
    else {
        qmLog.error("Please add your CUREDAO_PERSONAL_ACCESS_TOKEN environmental variable from " + env.getAppHostName()
            + "/api/v2/account");
    }
    return options;
}
function getBuildInfo() {
    return {
        androidVersionCode: qm.buildInfoHelper.buildInfo.versionNumbers.androidVersionCode,
        buildLink: testHelpers.getBuildLink(),
        buildServer: qmLog.getCurrentServerContext(),
        builtAt: timeHelper.getUnixTimestampInSeconds(),
        debugMode: isTruthy(process.env.APP_DEBUG),
        versionNumber: qm.buildInfoHelper.buildInfo.versionNumbers.ionicApp,
    };
}
function saveAppSettings() {
    qm.appsManager.getAppSettingsFromApi(env.getQMClientIdOrException(), function (AppSettingsResponse) {
        qm.staticData = AppSettingsResponse.staticData;
        var as = qm.staticData.appSettings;
        process.env.APP_DISPLAY_NAME = as.name; // Need env for Fastlane
        process.env.APP_IDENTIFIER = as.additionalSettings.appIds.appIdentifier; // Need env for Fastlane
        function addBuildInfoToAppSettings() {
            as.buildServer = qmLog.getCurrentServerContext();
            as.buildLink = testHelpers.getBuildLink();
            as.versionNumber = qm.buildInfoHelper.buildInfo.versionNumbers.ionicApp;
            as.androidVersionCode = qm.buildInfoHelper.buildInfo.versionNumbers.androidVersionCode;
            as.debugMode = isTruthy(process.env.APP_DEBUG);
            as.builtAt = timeHelper.getUnixTimestampInSeconds();
        }
        addBuildInfoToAppSettings();
        qmLog.info("Got app settings for " + as.appDisplayName + ". You can change your app settings at " +
            getAppEditUrl());
        var url = env.getAppHostName();
        if (url) {
            as.apiOrigin = url.replace("https://", "");
        }
        return writeAppSettingsToFile(qm.staticData.appSettings);
    }).catch(function (error) {
        qmLog.error(error);
    });
}
exports.saveAppSettings = saveAppSettings;
function getAppEditUrl() {
    return getAppsListUrl() + "?clientId=" + qm.getClientId();
}
function getAppsListUrl() {
    return "https://builder.quantimo.do/#/app/configuration";
}
function getAppDesignerUrl() {
    return "https://builder.quantimo.do/#/app/configuration?clientId=" + qm.getClientId();
}
function writeAppSettingsToFile(appSettings) {
    qm.staticData = qm.staticData || {};
    qm.staticData.buildInfo = getBuildInfo();
    appSettings = appSettings || qm.staticData.appSettings;
    var content = "// noinspection DuplicatedCode\nif(typeof qm === \"undefined\"){if(typeof window === \"undefined\") {global.qm = {}; }else{window.qm = {};}}\nif(typeof qm.staticData === \"undefined\"){qm.staticData = {};}\nqm.staticData.appSettings = " + qmLog.prettyJSONStringify(appSettings);
    try {
        fileHelper.writeToFile(env.paths.www.appSettings, content);
    }
    catch (e) {
        // @ts-ignore
        qmLog.error(e.message + ".  Maybe www/data doesn't exist but it might be resolved when we copy from public");
    }
    try {
        fileHelper.writeToFile("build/chrome_extension/data/appSettings.js", content);
    }
    catch (e) {
        // @ts-ignore
        qmLog.error(e.message + ".  Maybe build/chrome_extension/data doesn't exist but it might be resolved when we copy from public");
    }
    return fileHelper.writeToFile(env.paths.src.appSettings, content);
}
exports.writeAppSettingsToFile = writeAppSettingsToFile;
//# sourceMappingURL=qm.app-settings.js.map