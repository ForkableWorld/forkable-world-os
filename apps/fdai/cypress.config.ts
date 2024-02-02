import {defineConfig} from "cypress";
import {cypressCodegen} from "cypress-codegen/plugin"
import {loadEnvFromDopplerOrDotEnv} from "./public/app/ts/env-helper"
import * as path from "path"
import * as cySocialLogins from "cypress-social-logins"
const envPath = path.resolve(__dirname, ".env")
loadEnvFromDopplerOrDotEnv(envPath)
//import * as qmLog from "./ts/qm.log;
const {env} = process;
let baseUrl = env.CYPRESS_BASE_URL
if(!baseUrl) {baseUrl = env.APP_URL}
// = "https://staging.quantimo.do"
if(!baseUrl) {throw new Error("CYPRESS_BASE_URL is not set")}
// @ts-ignore
let cypressEnvs: { [p: string]: any } = {
	OAUTH_APP_ORIGIN: env.OAUTH_APP_ORIGIN ||  'http://localhost:5000',
	abort_strategy: true,
	"FAIL_FAST_ENABLED": env.FAIL_FAST_ENABLED || false, // https://github.com/javierbrea/cypress-fail-fast
	CODEGEN: true, // https://github.com/ExpediaGroup/cypress-codegen
	"hars_folders": "cypress/hars", // https://github.com/NeuraLegion/cypress-har-generator
	snapshotOnly: false,
	hideXHRInCommandLog: true,
}
cypressEnvs = Object.assign(env, cypressEnvs)
let e2e: Cypress.EndToEndConfigOptions = {
	setupNodeEvents(on, config) {
		require("cypress-localstorage-commands/plugin")(on, config);
		on('task', {
			GoogleSocialLogin: cySocialLogins.plugins.GoogleSocialLogin,
			GitHubSocialLogin: cySocialLogins.plugins.GitHubSocialLogin
		})
		require("cypress-fail-fast/plugin")(on, config);
		cypressCodegen(on, config);
		// implement node event listeners here
		const commit = process.env.COMMIT_SHA || process.env.GITHUB_SHA
		const token = process.env.GITHUB_TOKEN || process.env.PERSONAL_GH_TOKEN
		const owner = process.env.GITHUB_REPOSITORY_OWNER || 'mikepsinn'
		let repo = process.env.GITHUB_REPOSITORY || 'cd-api'
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
			console.info("cypress-set-github-status:", config);
		}
		//console.info("setupNodeEvents Config:", config);
		return config;
	},
	experimentalStudio: true,
	"chromeWebSecurity": false,
	baseUrl,
	projectId: env.CYPRESS_PROJECT_ID || env.CURRENTS_PROJECT_ID,
	"pageLoadTimeout": 60000,
	"videoCompression": false,
	"videoUploadOnPasses": false,
	//"experimentalSessionAndOrigin": false,
	"video": true,
	"env": cypressEnvs,
	"reporter": "cypress-multi-reporters",
	"reporterOptions": {
		"configFile": "cypress/reporterOpts.json"
	},
	"screenshotsFolder": "cypress/reports/assets"
}
let cypressConfig: Cypress.ConfigOptions = {e2e: e2e,};
export default defineConfig(cypressConfig);
