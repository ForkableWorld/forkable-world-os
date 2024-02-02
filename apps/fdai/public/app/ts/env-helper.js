"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.qmPlatform = exports.getGithubAccessToken = exports.getAccessToken = exports.getAppHostName = exports.getQMClientSecret = exports.getQMClientIdIfSet = exports.getQMClientIdOrException = exports.loadEnv = exports.getEnvOrException = exports.loadEnvFromDopplerOrDotEnv = exports.getenv = exports.getRequiredEnv = exports.paths = exports.envNames = void 0;
var dotenv_1 = require("dotenv");
var qm_file_helper_1 = require("./qm.file-helper");
var fileHelper = require("./qm.file-helper");
var qmLog = require("./qm.log");
exports.envNames = {
    STORAGE_ACCESS_KEY_ID: "STORAGE_ACCESS_KEY_ID",
    STORAGE_SECRET_ACCESS_KEY: "STORAGE_SECRET_ACCESS_KEY",
    BUGSNAG_API_KEY: "BUGSNAG_API_KEY",
    CONNECTOR_QUANTIMODO_CLIENT_ID: "CONNECTOR_QUANTIMODO_CLIENT_ID",
    CONNECTOR_QUANTIMODO_CLIENT_SECRET: "CONNECTOR_QUANTIMODO_CLIENT_SECRET",
    CUREDAO_PERSONAL_ACCESS_TOKEN: "CUREDAO_PERSONAL_ACCESS_TOKEN",
    CURRENTS_RECORD_KEY: "CURRENTS_RECORD_KEY",
    CYPRESS_PROJECT_ID: "CYPRESS_PROJECT_ID",
    EXPRESS_ORIGIN: "EXPRESS_ORIGIN",
    EXPRESS_PORT: "EXPRESS_PORT",
    GH_TOKEN: "GH_TOKEN",
    GITHUB_TOKEN: "GITHUB_TOKEN",
    GITHUB_ACCESS_TOKEN: "GITHUB_ACCESS_TOKEN",
    GITHUB_ACCESS_TOKEN_FOR_STATUS: "GITHUB_ACCESS_TOKEN_FOR_STATUS",
    LOGIN_SUCCESS_REDIRECT: "LOGIN_SUCCESS_REDIRECT",
    QM_API_ORIGIN: "QM_API_ORIGIN",
    QM_STORAGE_ACCESS_KEY_ID: "QM_STORAGE_ACCESS_KEY_ID",
    QM_STORAGE_SECRET_ACCESS_KEY: "QM_STORAGE_SECRET_ACCESS_KEY",
};
exports.paths = {
    apk: {
        arm7Release: "platforms/android/app/build/outputs/apk/release/app-arm7-release.apk",
        builtApk: null,
        combinedDebug: "platforms/android/app/build/outputs/apk/release/app-debug.apk",
        combinedRelease: "platforms/android/app/build/outputs/apk/release/app-release.apk",
        outputFolder: "platforms/android/app/build/outputs/apk",
        x86Release: "platforms/android/app/build/outputs/apk/release/app-x86-release.apk",
    },
    sass: ["./public/scss/**/*.scss"],
    src: {
        appSettings: "public/data/appSettings.js",
        data: "public/data",
        defaultPrivateConfig: "public/default.private_config.json",
        devCredentials: "public/dev-credentials.json",
        firebase: "public/lib/firebase/**/*",
        icons: "public/img/icons",
        js: "public/js/*.js",
        serviceWorker: "public/firebase-messaging-sw.js",
    },
    www: {
        appSettings: "public/data/appSettings.js",
        data: "www/data",
        defaultPrivateConfig: "www/default.private_config.json",
        devCredentials: "www/dev-credentials.json",
        firebase: "www/lib/firebase/",
        icons: "www/img/icons",
        js: "www/js/",
        scripts: "www/scripts",
    },
};
function getRequiredEnv(envName) {
    return getEnvOrException(envName);
}
exports.getRequiredEnv = getRequiredEnv;
function getenv(names, defaultValue) {
    if (!Array.isArray(names)) {
        names = [names];
    }
    function getFromProcess() {
        // tslint:disable-next-line:prefer-for-of
        for (var i = 0; i < names.length; i++) {
            var name_1 = names[i];
            var val = process.env[name_1];
            if (typeof val !== "undefined" && val !== null && val !== "") {
                // @ts-ignore
                return val;
            }
        }
        return null;
    }
    var result = getFromProcess();
    if (result !== null) {
        return result;
    }
    try {
        loadEnv(".env");
        result = getFromProcess();
        if (result !== null) {
            return result;
        }
        console.info("Could not get " + names.join(" or ") + " from .env file or process.env");
    }
    catch (e) {
        // @ts-ignore
        console.info(e.message + "\n No .env to get " + names.join(" or "));
    }
    return defaultValue || null;
}
exports.getenv = getenv;
function loadEnvFromDopplerOrDotEnv(relativeEnvPath) {
    if (!process.env.DOPPLER_TOKEN) {
        console.info("DOPPLER_TOKEN not set so loading ENV FROM ".concat(relativeEnvPath));
        try {
            loadEnv(relativeEnvPath);
        }
        catch (e) {
            qmLog.error("Please provide a DOPPLER_TOKEN environment variable or .env in root of repo");
            throw e;
        }
    }
    else {
        console.info("DOPPLER_TOKEN is set so loading ENV FROM DOPPLER");
        loadDopplerSecrets();
    }
}
exports.loadEnvFromDopplerOrDotEnv = loadEnvFromDopplerOrDotEnv;
function getEnvOrException(names) {
    if (!Array.isArray(names)) {
        names = [names];
    }
    var val = getenv(names);
    if (val === null || val === "" || val === "undefined") {
        var msg = "\n==================================================================\nPlease specify\n" + names.join(" or ") + "\nin .env file in root of project or system environmental variables\n==================================================================\n";
        qmLog.throwError(msg);
        throw new Error(msg);
    }
    return val;
}
exports.getEnvOrException = getEnvOrException;
function loadEnv(relativeEnvPath) {
    if (!relativeEnvPath) {
        relativeEnvPath = ".env";
        var count = 0;
        while (!fileHelper.exists(relativeEnvPath)) {
            count++;
            if (count > 10) {
                throw Error("Could not find .env file");
            }
            relativeEnvPath = "../" + relativeEnvPath;
        }
    }
    var path = fileHelper.getAbsolutePath(relativeEnvPath);
    console.info("Loading " + path);
    // https://github.com/motdotla/dotenv#what-happens-to-environment-variables-that-were-already-set
    var result = dotenv_1.default.config({ path: path });
    if (result.error) {
        throw result.error;
    }
    // qmLog.info(result.parsed.name)
}
exports.loadEnv = loadEnv;
function getQMClientIdOrException() {
    return getEnvOrException(exports.envNames.CONNECTOR_QUANTIMODO_CLIENT_ID);
}
exports.getQMClientIdOrException = getQMClientIdOrException;
function getQMClientIdIfSet() {
    return getenv(exports.envNames.CONNECTOR_QUANTIMODO_CLIENT_ID);
}
exports.getQMClientIdIfSet = getQMClientIdIfSet;
function getQMClientSecret() {
    return getenv(exports.envNames.CONNECTOR_QUANTIMODO_CLIENT_SECRET);
}
exports.getQMClientSecret = getQMClientSecret;
function getAppHostName() {
    return getenv(exports.envNames.QM_API_ORIGIN);
}
exports.getAppHostName = getAppHostName;
function getAccessToken() {
    return getEnvOrException(exports.envNames.CUREDAO_PERSONAL_ACCESS_TOKEN);
}
exports.getAccessToken = getAccessToken;
function getGithubAccessToken() {
    return getEnvOrException([
        exports.envNames.GITHUB_ACCESS_TOKEN_FOR_STATUS,
        exports.envNames.GITHUB_ACCESS_TOKEN,
        exports.envNames.GH_TOKEN,
        exports.envNames.GITHUB_TOKEN,
    ]);
}
exports.getGithubAccessToken = getGithubAccessToken;
exports.qmPlatform = {
    android: "android",
    buildingFor: {
        getPlatformBuildingFor: function () {
            if (exports.qmPlatform.buildingFor.android()) {
                return "android";
            }
            if (exports.qmPlatform.buildingFor.ios()) {
                return "ios";
            }
            if (exports.qmPlatform.buildingFor.chrome()) {
                return "chrome";
            }
            if (exports.qmPlatform.buildingFor.web()) {
                return "web";
            }
            qmLog.error("What platform are we building for?");
            return null;
        },
        setChrome: function () {
            exports.qmPlatform.buildingFor.platform = exports.qmPlatform.chrome;
        },
        platform: "",
        web: function () {
            return !exports.qmPlatform.buildingFor.android() &&
                !exports.qmPlatform.buildingFor.ios() &&
                !exports.qmPlatform.buildingFor.chrome();
        },
        android: function () {
            if (exports.qmPlatform.buildingFor.platform === "android") {
                return true;
            }
            if (process.env.BUDDYBUILD_SECURE_FILES) {
                return true;
            }
            if (process.env.TRAVIS_OS_NAME === "osx") {
                return false;
            }
            return process.env.BUILD_ANDROID;
        },
        ios: function () {
            if (exports.qmPlatform.buildingFor.platform === exports.qmPlatform.ios) {
                return true;
            }
            if (process.env.BUDDYBUILD_SCHEME) {
                return true;
            }
            if (process.env.TRAVIS_OS_NAME === "osx") {
                return true;
            }
            return process.env.BUILD_IOS;
        },
        chrome: function () {
            if (exports.qmPlatform.buildingFor.platform === exports.qmPlatform.chrome) {
                return true;
            }
            return process.env.BUILD_CHROME;
        },
        mobile: function () {
            return exports.qmPlatform.buildingFor.android() || exports.qmPlatform.buildingFor.ios();
        },
    },
    chrome: "chrome",
    setBuildingFor: function (platform) {
        exports.qmPlatform.buildingFor.platform = platform;
    },
    isOSX: function () {
        return process.platform === "darwin";
    },
    isLinux: function () {
        return process.platform === "linux";
    },
    isWindows: function () {
        return !exports.qmPlatform.isOSX() && !exports.qmPlatform.isLinux();
    },
    getPlatform: function () {
        if (exports.qmPlatform.buildingFor) {
            return exports.qmPlatform.buildingFor;
        }
        if (exports.qmPlatform.isOSX()) {
            return exports.qmPlatform.ios;
        }
        if (exports.qmPlatform.isWindows()) {
            return exports.qmPlatform.android;
        }
        return exports.qmPlatform.web;
    },
    ios: "ios",
    web: "web",
    isBackEnd: function () {
        return typeof window === "undefined";
    },
};
function loadDopplerSecrets() {
    var result;
    var absPath = (0, qm_file_helper_1.getAbsolutePath)("doppler-secrets-async.js");
    result = require("child_process").execSync("node " + absPath);
    var secrets = JSON.parse(result);
    qmLog.info("Setting envs from ".concat(absPath, "..."));
    var env = process.env;
    Object.keys(secrets).forEach(function (key) {
        if (secrets.hasOwnProperty(key)) {
            var value = secrets[key];
            var existingValue = env[key] || null;
            if (existingValue && existingValue.length > 0) {
                qmLog.info(key + " already set to " + existingValue);
                return;
            }
            if (value.length > 6) {
                qmLog.debug(key + "=..." + value.substring(value.length - 6, value.length));
            }
            else {
                qmLog.debug(key + "=" + value);
            }
            process.env[key] = secrets[key];
        }
    });
}
