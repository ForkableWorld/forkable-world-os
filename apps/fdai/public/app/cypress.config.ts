import {defineConfig} from "cypress";
import * as envHelper from "./ts/env-helper";
import * as cySocialLogins from "cypress-social-logins"
envHelper.loadEnvFromDopplerOrDotEnv(null);
//import * as qmLog from "./ts/qm.log;
const {env} = process;
let baseUrl = envHelper.getRequiredEnv("EXPRESS_ORIGIN");
let apiOrigin = env.QM_API_ORIGIN || "https://app.quantimo.do";
let appOrigin = env.OAUTH_APP_ORIGIN || baseUrl;
let builderOrigin = env.BUILDER_ORIGIN || appOrigin
let e2e = {
    setupNodeEvents(on, config) {
		on('task', {
			GoogleSocialLogin: cySocialLogins.plugins.GoogleSocialLogin,
			GitHubSocialLogin: cySocialLogins.plugins.GitHubSocialLogin
		})
        const commit = process.env.COMMIT_SHA || process.env.GITHUB_SHA
        const token = process.env.GITHUB_TOKEN || process.env.PERSONAL_GH_TOKEN
        const owner = process.env.GITHUB_REPOSITORY_OWNER || 'mikepsinn'
        let repo = process.env.GITHUB_REPOSITORY || 'cd-ionic'
        if(repo.includes('/')){
            const repoParts = repo.split('/')
            repo = repoParts[1]
        }
        const commonStatus = process.env.COMMON_STATUS || 'Cypress E2E tests'
        if(!commit || !token){
            console.error("Missing commit or token for cypress-set-github-status");
        } else {
            let statusVars: { owner: string; repo: string; commit: string; commonStatus: string; token: string } = {
                owner,
                repo,
                commit,
                token,
                // when finished the test run, after reporting its machine status
                // also set or update the common final status
                commonStatus,
            }
            require('cypress-set-github-status')(on, config, statusVars)
            console.info("cypress-set-github-status:", statusVars);
        }
        //console.info("setupNodeEvents Config:", config);
        return config;
    },
    experimentalStudio: true,
    "chromeWebSecurity": false,
    "baseUrl": baseUrl,
    "projectId": env.CURRENTS_PROJECT_ID || env.CYPRESS_IO_PROJECT_ID,
    "pageLoadTimeout": 60000,
    "videoUploadOnPasses": false,
    //"experimentalSessionAndOrigin": false,
    "video": true,
    "env": {
        "QM_API_ORIGIN": apiOrigin,
        "OAUTH_APP_ORIGIN": appOrigin,
        "BUILDER_ORIGIN": builderOrigin,
        "abort_strategy": true,
            "FAIL_FAST_ENABLED": env.FAIL_FAST_ENABLED, // https://github.com/javierbrea/cypress-fail-fast
            CODEGEN: true, // https://github.com/ExpediaGroup/cypress-codegen
            "hars_folders": "cypress/hars", // https://github.com/NeuraLegion/cypress-har-generator
            snapshotOnly: false,
        hideXHRInCommandLog: true,
    },
    "reporter": "cypress-multi-reporters",
    "reporterOptions": {
        "configFile": "cypress/reporterOpts.json"
    },
    "screenshotsFolder": "cypress/reports/assets"
}
let cypressConfig: Cypress.ConfigOptions = {e2e: e2e,};
export default defineConfig(cypressConfig);
