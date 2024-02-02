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
exports.writeBuildInfoFile = exports.getCurrentBuildInfo = void 0;
var env_helper_1 = require("./env-helper");
var qm_file_helper_1 = require("./qm.file-helper");
var gitHelper = __importStar(require("./qm.git"));
var qmLog = __importStar(require("./qm.log"));
var timeHelper = __importStar(require("./qm.time-helper"));
var th = __importStar(require("./test-helpers"));
var majorMinorVersionNumbers = "2.10.";
var date = new Date();
var minorNum = getMinutesSinceMidnight() * 99 / 1440;
minorNum = Math.round(minorNum);
var minor = appendLeadingZero(minorNum);
function getPatchVersionNumber() {
    var monthNumber = (date.getMonth() + 1).toString();
    var dayOfMonth = ("0" + date.getDate()).slice(-2);
    return monthNumber + dayOfMonth;
}
function getIosMinorVersionNumber() {
    return (getMinutesSinceMidnight()).toString();
}
function getMinutesSinceMidnight() {
    return date.getHours() * 60 + date.getMinutes();
}
function appendLeadingZero(integer) { return ("0" + integer).slice(-2); }
function getLongDateFormat() {
    return date.getFullYear().toString() + appendLeadingZero(date.getMonth() + 1) + appendLeadingZero(date.getDate());
}
function getCurrentBuildInfo() {
    return {
        buildLink: th.getBuildLink(),
        buildServer: qmLog.getCurrentServerContext(),
        builtAt: timeHelper.getUnixTimestampInSeconds(),
        builtAtString: new Date().toISOString(),
        gitBranch: gitHelper.getBranchName(),
        gitCommitShaHash: gitHelper.getCurrentGitCommitSha(),
        iosCFBundleVersion: majorMinorVersionNumbers + getPatchVersionNumber() + "." + getIosMinorVersionNumber(),
        versionNumber: majorMinorVersionNumbers + getPatchVersionNumber(),
        versionNumbers: {
            androidVersionCode: getLongDateFormat() + minor,
            // androidVersionCodes: {armV7: getLongDateFormat() + appendLeadingZero(date.getHours()),
            // x86: getLongDateFormat() + appendLeadingZero(date.getHours() + 1)},
            ionicApp: majorMinorVersionNumbers + getPatchVersionNumber(),
            iosCFBundleVersion: majorMinorVersionNumbers + getPatchVersionNumber() + "." + getIosMinorVersionNumber(),
        },
    };
}
exports.getCurrentBuildInfo = getCurrentBuildInfo;
function writeBuildInfoFile() {
    var as = getCurrentBuildInfo();
    var content = 'if(typeof qm === "undefined"){if(typeof window === "undefined") {global.qm = {}; }else{window.qm = {};}}\n' +
        'if(typeof qm.staticData === "undefined"){qm.staticData = {};}\n' +
        "qm.staticData.buildInfo =" + qmLog.prettyJSONStringify(as);
    try {
        (0, qm_file_helper_1.writeToFile)(env_helper_1.paths.www.data + "/buildInfo.js", content);
    }
    catch (e) {
        // @ts-ignore
        qmLog.error(e.message + ".  Maybe www/data doesn't exist but it might be resolved when we copy from public");
    }
    try {
        (0, qm_file_helper_1.writeToFile)("build/chrome_extension/data/buildInfo.js", content);
    }
    catch (e) {
        // @ts-ignore
        qmLog.error(e.message + ".  Maybe build/chrome_extension/data doesn't exist but it might be resolved when we copy from public");
    }
    return (0, qm_file_helper_1.writeToFile)(env_helper_1.paths.src.data + "/buildInfo.js", content);
}
exports.writeBuildInfoFile = writeBuildInfoFile;
//# sourceMappingURL=qm.build-info-helper.js.map