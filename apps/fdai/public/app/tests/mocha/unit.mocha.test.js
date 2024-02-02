"use strict"
var REPORT_EVERY_TEST_RESULT = false
Object.defineProperty(exports, "__esModule", { value: true })
var path = require('path')
var appDir = path.resolve(".")
var chai = require("chai")
var expect = chai.expect
// Otherwise assertion failures in async tests are wrapped, which prevents mocha from
// being able to interpret them (such as displaying a diff).
process.on('unhandledRejection', function(err) {
    if(typeof err !== "string"){
        //qmLog.error("Error is not as string but is: ", null, {err})
        throw err
    }
    if(err.indexOf("unhandledRejection: Uncaught FetchError: invalid json response body") !== -1){
        qmLog.error(err)
    } else {
        throw err
    }
})
const envHelper = require("../../ts/env-helper");
envHelper.loadEnvFromDopplerOrDotEnv(null)
var qmGit = require("../../ts/qm.git")
var qmShell = require("../../ts/qm.shell")
var fileHelper = global.fileHelper = require("../../ts/qm.file-helper")
var cypressFunctions = require("../../cypress/cypress-functions")
var urlParser = require("url")
var https = require("https")
global.fetch = require("../../node_modules/node-fetch/lib/index.js")
global.Headers = fetch.Headers
var _str = require("underscore.string")
var simpleGit = require("simple-git")
var th = require("../../ts/test-helpers")
var git = simpleGit()
global.bugsnagClient = require('./../../node_modules/@bugsnag/node')
var argv = require('./../../node_modules/yargs').argv
global.qm = require('../../public/js/qmHelpers')
qm.appMode.mode = 'mocha'
global.qmLog = require('../../public/js/qmLogger')
qmLog.color = require('./../../node_modules/ansi-colors')
qm.github = require('github-api')
qm.Quantimodo = require('./../../node_modules/quantimodo')
require('../../public/data/appSettings.js')
require('../../public/data/commonVariables.js')
require('../../public/data/connectors.js')
require('../../public/data/dialogAgent.js')
require('../../public/data/qmStates.js')
require('../../public/data/units.js')
require('../../public/data/variableCategories.js')
require('../../public/data/stateNames.js')
qm.stateNames = qm.staticData.stateNames
qm.qmLog = qmLog
qmLog.qm = qm
qm.qmLog.setLogLevelName(process.env.LOG_LEVEL || 'info')
global.nlp = require('compromise')
var Q = global.Q = require('q')
global.Swal = require('./../../node_modules/sweetalert2/dist/sweetalert2.all')
//global.moment = require('./../../public/lib/moment/moment')
global.moment = require('moment-timezone')
const chrome = require('sinon-chrome/extensions')
const {getBuildLink} = require("../../ts/test-helpers")
const db = require("../../db");
const authHelper = require("../../utils/authHelper");
const connectionHelper = require("../../utils/connectionHelper")

var qmTests = {
    getTestAccessToken(){
        var t = process.env.TEST_ACCESS_TOKEN
        if(!t){
            t = "test-token"
        }
        return t
    },
    setTestAccessToken(){
        qm.auth.logout()
        qm.auth.setAccessToken(qmTests.getTestAccessToken())
    },
    setDemoAccessToken(){
        qm.auth.logout()
        qm.auth.setAccessToken("demo")
    },
    testParams: {},
    setTestParams(params){
        qmTests.testParams = params
        qmLog.debug("Setting test params: " + JSON.stringify(params))
    },
    getTestParams(){
        if(typeof qmTests.testParams === 'string'){
            return JSON.parse(qmTests.testParams)
        }
        return qmTests.testParams
    },
    startUrl: null,
    getStartUrl(){
        var params = qmTests.getTestParams()
        var startUrl = 'https://medimodo.herokuapp.com'
        if(params && params.startUrl){ startUrl = params.startUrl }
        if(params && params.deploy_ssl_url){ startUrl = params.deploy_ssl_url }
        if(params && params.START_URL){ startUrl = params.START_URL }
        if(process.env.START_URL){ startUrl = process.env.START_URL }
        if(process.env.DEPLOY_PRIME_URL){ startUrl = process.env.DEPLOY_PRIME_URL }
        if(argv.startUrl){ startUrl = argv.startUrl }
        if(startUrl.indexOf('https') === -1){ startUrl = "https://" + startUrl }
        return startUrl
    },
    getSha(){
        var params = qmTests.getTestParams()
        if(params && params.commit_ref){ return params.commit_ref }
        if(params && params.sha){ return params.sha }
    },
    getStatusesUrl(){
        var params = qmTests.getTestParams()
        if(params && params.statuses_url){ return params.statuses_url }
        /** @namespace params.commit_url */
        if(params && params.commit_url){
            var url = params.commit_url
            url = url.replace('github.com', 'api.github.com/repos')
            url = url.replace('commit', 'statuses')
            return url
        }
        return null
    },
    getApiOrigin(){
        var params = qmTests.getTestParams()
        if(params && params.QM_API_ORIGIN){ return params.QM_API_ORIGIN }
        if(process.env.QM_API_ORIGIN){ return process.env.QM_API_ORIGIN }
        if(argv.apiOrigin){ return argv.apiOrigin }
        return 'api.quantimo.do'
    },
    tests: {
        checkIntent(userInput, expectedIntentName, expectedEntities, expectedParameters, callback){
            var intents = qm.staticData.dialogAgent.intents
            var entities = qm.staticData.dialogAgent.entities
            info("Got " + entities.length + " entities")
            var matchedEntities = qm.dialogFlow.getEntitiesFromUserInput(userInput)
            for (var expectedEntityName in expectedEntities) {
                if (!expectedEntities.hasOwnProperty(expectedEntityName)) { continue }
                qm.assert.doesNotEqual(typeof matchedEntities[expectedEntityName], "undefined",
                    expectedEntityName + " not in matchedEntities!")
                qm.assert.equals(matchedEntities[expectedEntityName].matchedEntryValue, expectedEntities[expectedEntityName])
            }
            var expectedIntent = intents[expectedIntentName]
            var triggerPhraseMatchedIntent = qm.dialogFlow.getIntentMatchingCommandOrTriggerPhrase(userInput)
            qm.assert.equals(triggerPhraseMatchedIntent.name, expectedIntentName)
            var score = qm.dialogFlow.calculateScoreAndFillParameters(expectedIntent, matchedEntities, userInput)
            var filledParameters = expectedIntent.parameters
            var expectedParameterName
            for (expectedParameterName in expectedParameters) {
                if (!expectedParameters.hasOwnProperty(expectedParameterName)) { continue }
                if(typeof filledParameters[expectedParameterName] === "undefined"){
                    score = qm.dialogFlow.calculateScoreAndFillParameters(expectedIntent, matchedEntities, userInput)
                }
                qm.assert.doesNotEqual(typeof filledParameters[expectedParameterName], "undefined", expectedParameterName + " not in filledParameters!")
                qm.assert.equals(filledParameters[expectedParameterName], expectedParameters[expectedParameterName])
            }
            qm.assert.greaterThan(-2, score)
            var matchedIntent = qm.dialogFlow.getIntent(userInput)
            filledParameters = matchedIntent.parameters
            qm.assert.equals(matchedIntent.name, expectedIntentName)
            for (expectedParameterName in expectedParameters) {
                if (!expectedParameters.hasOwnProperty(expectedParameterName)) { continue }
                qm.assert.doesNotEqual(typeof filledParameters[expectedParameterName], "undefined", expectedParameterName + " not in filledParameters!")
                qm.assert.equals(filledParameters[expectedParameterName], expectedParameters[expectedParameterName])
            }
            if(callback){ callback() }
        },
        getOptions(startUrl){
            var options = {}
            options.startUrl = startUrl || qmTests.getStartUrl()
            options.apiOrigin = qmTests.getApiOrigin()
            if(qmTests.getSha()){ options.sha = qmTests.getSha() }
            if(qmTests.getStatusesUrl()){ options.statuses_url = qmTests.getStatusesUrl() }
            return options
        },
    },
    logBugsnagLink(suite, start, end){
        var query = "filters[event.since][0]=" +
            start + "&filters[error.status][0]=open&filters[event.before][0]=" +
            end + "&sort=last_seen"
        console.error(suite.toUpperCase() + " errors: https://app.bugsnag.com/quantimodo/" + suite + "/errors?" + query)
    },
    outputErrorsForTest(testResults){
        var name = testResults.testName || testResults.name
        console.error(name + " FAILED: https://app.ghostinspector.com/results/" + testResults._id)
        qmTests.logBugsnagLink('ionic', testResults.dateExecutionStarted, testResults.dateExecutionFinished)
        qmTests.logBugsnagLink('slim-api', testResults.dateExecutionStarted, testResults.dateExecutionFinished)
        console.error("=== CONSOLE ERRORS ====")
        for (var i = 0; i < testResults.console.length; i++) {
            var logObject = testResults.console[i]
            if(logObject.error || logObject.output.toLowerCase().indexOf("error") !== -1){
                console.error(logObject.output + " at " + logObject.url)
            }
        }
        process.exit(1)
    },
    runAllTestsForType (testType, callback) {
        console.info("=== " + testType + " Tests ===")
        var tests = qm.tests[testType]
        for (var testName in tests) {
            if (!tests.hasOwnProperty(testName)) continue
            console.info(testName + "...")
            tests[testName]()
            console.info(testName + " passed! :D")
        }
        if(callback){ callback() }
    },
}
var context = "qm.mocha.test.js"
before(function (done) {
    qmGit.setGithubStatus("pending", context, "Running...", getBuildLink(), function (res) {
        qmLog.debug(res)
        done()
    })
})
beforeEach(function (done) {
    var t = this.currentTest
    this.timeout(10000) // Default 2000 is too fast for Github API
    // @ts-ignore
    if(REPORT_EVERY_TEST_RESULT){
        qmGit.setGithubStatus("pending", t.title, "Running...", getBuildLink(), function (res) {
            qmLog.debug(res)
            done()
        })
    } else {
        done()
    }
})
var failedTests = []
var successfulTests = []
afterEach(function (done) {
    var t = this.currentTest
    // @ts-ignore
    var state = t.state
    if (!state) {
        qmLog.error("No test state in afterEach!")
        done()
        return
    }
    if (state === "failed") {
        failedTests.push(t)
    } else if (!REPORT_EVERY_TEST_RESULT) {
        successfulTests.push(t)
    }
    if (state === "failed" || REPORT_EVERY_TEST_RESULT) {
        // @ts-ignore
        qmGit.setGithubStatus((state === "failed") ? "failure" : "success", t.title, t.title, getBuildLink(), function (res) {
            qmLog.debug(res)
            done()
        })
    } else {
        done()
    }
})
after(function(done){
    if (failedTests.length) {
        qmGit.setGithubStatus("failure", context, failedTests.length + " failed tests in after() hook!", getBuildLink(), function (res) {
            qmLog.debug(res)
            done()
        })
        return
    }
    if (!successfulTests.length) {
        // eslint-disable-next-line no-debugger
        debugger
        qmGit.setGithubStatus("error", context, "No successfulTests or failedTests in after() hook!", getBuildLink(), function (res) {
            qmLog.debug(res)
            done()
        })
        return
    }
    qmGit.setGithubStatus("success", context, successfulTests.length + " tests passed!", getBuildLink(), function (res) {
        qmLog.debug(res)
        done()
    })
})
function downloadFileContains(url, expectedToContain) {
    const deferred = Q.defer()
    downloadFile(url)
        .then(function (str) {
            expect(str).to.contain(expectedToContain)
            deferred.resolve(str)
        })
    return deferred.promise
}
function downloadFile(url) {
    const deferred = Q.defer()
    var parsedUrl = urlParser.parse(url)
    var options = {
        hostname: parsedUrl.hostname,
        method: "GET",
        path: parsedUrl.path,
        port: 443,
    }
    var req = https.request(options, function (res) {
        console.log("statusCode: " + res.statusCode)
        expect(res.statusCode).to.eq(200)
        var str = ""
        res.on("data", function (chunk) {
            str += chunk
        })
        res.on("end", function () {
            console.log("RESPONSE: " + _str.truncate(str, 30))
            deferred.resolve(str)
        })
    })
    req.on("error", function (error) {
        console.error(error)
        deferred.reject(error)
    })
    req.end()
    return deferred.promise
}
function expectInteger(val){
    expect(val).to.be.a('number')
    expect(val % 1).to.equal(0)
}
function info(str, meta){
    qmLog.info("MOCHA: " + str, meta)
}
function checking(str, meta){
    info("Checking that " + str + "...", meta)
}

describe("Measurement", function () {
    function getBupropionMeasurement(startAt){
        return {
            "combinationOperation": "SUM",
            "inputType": "value",
            "pngPath": "https://static.quantimo.do/img/variable_categories/treatments.png",
            startAt,
            "unitAbbreviatedName": "mg",
            "unitId": 7,
            "unitName": "Milligrams",
            "upc": null,
            "valence": null,
            "value": 150,
            "variableCategoryId": "Treatments",
            "variableCategoryName": "Treatments",
            "variableName": "Bupropion Sr",
            "note": "",
        }
    }
    it('can add to measurement queue and round startAt', function () {
        var startAt = "2020-12-01 15:00:00"
        var m = getBupropionMeasurement(startAt)
        expect(qm.measurements.getStartAt(m)).to.eq(startAt)
        qm.measurements.addToMeasurementsQueue(m)
        qm.lei(!qm.measurements.queue[m.variableName][m.startAt])
        var queue = qm.measurements.getMeasurementsFromQueue()
        expect(queue).length(1)
        queue.forEach(function(m){
            expect(m.startAt).to.eq(startAt)
            // TODO: Uncomment this qm.lei(m.startTime)
            //qm.lei(m.startTimeEpoch)
            //qm.lei(m.startTimeEpochSeconds)
        })
        qm.measurements.queue = {}
    })
})

describe("API", function (){
    it.skip("Makes sure api url is app.quantimo.do", function (done) {
        if(qm.appMode.isStaging()){
            expect(qm.api.getQMApiOrigin()).to.eq("https://staging.quantimo.do")
        } else {
            expect(qm.api.getQMApiOrigin()).to.eq("https://app.quantimo.do")
        }
        done()
    })
})
describe("Chrome Extension", function () {
    before(function () {
        global.chrome = chrome
    })
    it('can create a popup window', function(done) {
        info("TODO: Figure out how to mock chrome.extension.onMessage")
        done()
        //qm.chrome.initialize()
        //qmTests.runAllTestsForType('chrome', done)
    })
})
describe("Cypress", function () {
    it.skip('can upload Cypress video', function(done) {
        const specName = "test_spec"
        const relative = cypressFunctions.getVideoPath(specName)
        const downloadPath = 'tmp/download.mp4'
        let s3Url
        fileHelper.deleteFile(relative).then(function (){
            let exists = fileHelper.exists(relative)
            expect(exists).to.be.false
            return fileHelper.createFile(relative, "test video")
        }).then(function (){
            return cypressFunctions.uploadCypressVideo(specName)
        }).then(function (url){
            s3Url = url
            return fileHelper.deleteFile(downloadPath)
        }).then(function (){
            fileHelper.assertDoesNotExist(downloadPath)
            return fileHelper.download(s3Url, downloadPath)
        }).then(function (){
            fileHelper.assertExists(downloadPath)
            done()
        })
    })
})
describe("File Helper", function () {
    it("creates success file", function (done) {
        const filename = "success-file"
        fileHelper.deleteFile(filename)
            .then(function (){
                let exists = fileHelper.exists(filename)
                expect(exists).to.be.false
                th.createSuccessFile()
                    .then(function (){
                        exists = fileHelper.exists(filename)
                        expect(exists).to.be.true
                        done()
                    })
            })
    })
    it("determines the absolute path", function (done) {
        var abs = fileHelper.getAbsolutePath("tests/ionIcons.js")
        expect(abs).contains(appDir)
        done()
    })
    it("uploads a file", function () {
        //this.timeout(60000) // Default 2000 is too fast
        if(!envHelper.getenv("STORAGE_ACCESS_KEY_ID")){
            console.warn("Could not test 'uploads a file' STORAGE_ACCESS_KEY_ID is not set")
            //done()
            return;
        }
        return fileHelper.uploadToS3("tests/ionIcons.js", "tests/ionIcons.js")
            .then(function (url) {
                return downloadFileContains(url, "iosArrowUp")
            })
    })
    // Randomly fails
    it.skip("uploads test results", function (done) {
        if(!envHelper.getenv("STORAGE_ACCESS_KEY_ID")){
            console.error("Could not test 'uploads test results' STORAGE_ACCESS_KEY_ID is not set")
            done()
            return;
        }
        this.timeout(60000) // Default 2000 is too fast
        cypressFunctions.uploadMochawesome()
            .then(function(urls) {
                expect(urls.find(function(url){
                    return url.indexOf("/assets/app.css") !== -1
                })).is.not.null
                expect(urls.find(function(url){
                    return url.indexOf("/assets/app.js") !== -1
                })).is.not.null
                expect(urls).length.to.be.greaterThan(2)
                done()
            })
    })
})
describe("Ghost Inspector", function () {
    it.skip("runs tests on staging API", function (done) {
        var previouslySetApiOrigin = process.env.QM_API_ORIGIN || null
        if(previouslySetApiOrigin){
            expect(previouslySetApiOrigin).to.eq(qmTests.getApiOrigin())
        }
        delete process.env.QM_API_ORIGIN
        chai.assert.isUndefined(process.env.QM_API_ORIGIN)
        var originalReleaseStage = process.env.RELEASE_STAGE
        process.env.RELEASE_STAGE = "staging"
        var url = th.getApiOrigin()
        var stagingUrl = "https://staging.quantimo.do"
        expect(url).to.contain(stagingUrl)
        expect(qm.api.getQMApiOrigin()).to.contain(stagingUrl)
        if (previouslySetApiOrigin) {
            process.env.QM_API_ORIGIN = previouslySetApiOrigin
        }
        process.env.RELEASE_STAGE = originalReleaseStage
        done()
    })
})
describe("Git Helper", function () {
    it.skip("sets commit status", function (done) {
        qmGit.setGithubStatus("pending", "test context", "test description",
            "https://get-bent.com", function (res) {
            expect(res.status).to.eq(201)
            done()
        })
    })
    it.skip("creates a feature branch and deletes it", function (done) {
        var featureName = "test-feature"
        var branchName = "feature/" + featureName
        qmGit.createFeatureBranch("test-feature")
        git.branchLocal().then(function (branchSummary) {
            expect(branchSummary.all).to.contain(branchName)
            qmShell.executeSynchronously("git checkout -B develop", true)
            git.deleteLocalBranch(branchName).then(function () {
                git.branchLocal().then(function (branchSummary) {
                    expect(branchSummary.all).not.to.contain(branchName)
                    done()
                })
            })
        })
    })
})
describe("Intent Handler", function () {
    it('can record measurement from user speech command', function(done) {
        var userInput = "Record 1 Overall Mood"
        var expectedIntentName = 'Record Measurement Intent'
        var expectedEntities = {variableName: 'Overall Mood', recordMeasurementTriggerPhrase: "record"}
        var expectedParameters = {variableName: 'Overall Mood', value: 1}
        qmTests.tests.checkIntent(userInput, expectedIntentName, expectedEntities, expectedParameters, done)
    })
    it.skip('can remember stuff', function(done) {
        var userInput = "Remember where my keys are"
        var expectedIntentName = 'Remember Intent'
        var expectedEntities = {interrogativeWord: 'where', rememberCommand: "remember"}
        var expectedParameters = {memoryQuestion: 'where my keys are'}
        qmTests.tests.checkIntent(userInput, expectedIntentName, expectedEntities, expectedParameters, done)
    })
})

describe("Notifications", function () {
    it('can parse pushed tracking reminder notification', function(done) {
        // noinspection HtmlRequiredAltAttribute,RequiredAttributes,HtmlUnknownAttribute
        var pushData = {
            actions: '[{"longTitle":"Rate 3\\/5","callback":"trackThreeRatingAction","modifiedValue":3,"action":"track","foreground":false,"shortTitle":"3\\/5","image":"https:\\/\\/web.quantimo.do\\/img\\/rating\\/100\\/face_rating_button_100_ok.png","accessibilityText":"3\\/5","functionName":"track","html":"<md-tooltip>Rate 3\\/5<\\/md-tooltip><img class=\\"md-user-avatar\\" style=\\"height: 100%;\\" ng-public=\\"https:\\/\\/web.quantimo.do\\/img\\/rating\\/100\\/face_rating_button_100_ok.png\\"\\/>","id":"ratingnotificationbutton-button","parameters":{"value":3,"modifiedValue":3,"action":"track","unitAbbreviatedName":"\\/5","trackingReminderNotificationId":99354},"successToastText":"Recorded 3 out of 5","text":"3\\/5","title":"3\\/5","tooltip":"Rate 3\\/5"},{"longTitle":"Rate 2\\/5","callback":"trackTwoRatingAction","modifiedValue":2,"action":"track","foreground":false,"shortTitle":"2\\/5","image":"https:\\/\\/web.quantimo.do\\/img\\/rating\\/100\\/face_rating_button_100_sad.png","accessibilityText":"2\\/5","functionName":"track","html":"<md-tooltip>Rate 2\\/5<\\/md-tooltip><img class=\\"md-user-avatar\\" style=\\"height: 100%;\\" ng-public=\\"https:\\/\\/web.quantimo.do\\/img\\/rating\\/100\\/face_rating_button_100_sad.png\\"\\/>","id":"ratingnotificationbutton-button","parameters":{"value":2,"modifiedValue":2,"action":"track","unitAbbreviatedName":"\\/5","trackingReminderNotificationId":99354},"successToastText":"Recorded 2 out of 5","text":"2\\/5","title":"2\\/5","tooltip":"Rate 2\\/5"},{"longTitle":"Rate 4\\/5","callback":"trackFourRatingAction","modifiedValue":4,"action":"track","foreground":false,"shortTitle":"4\\/5","image":"https:\\/\\/web.quantimo.do\\/img\\/rating\\/100\\/face_rating_button_100_happy.png","accessibilityText":"4\\/5","functionName":"track","html":"<md-tooltip>Rate 4\\/5<\\/md-tooltip><img class=\\"md-user-avatar\\" style=\\"height: 100%;\\" ng-public=\\"https:\\/\\/web.quantimo.do\\/img\\/rating\\/100\\/face_rating_button_100_happy.png\\"\\/>","id":"ratingnotificationbutton-button","parameters":{"value":4,"modifiedValue":4,"action":"track","unitAbbreviatedName":"\\/5","trackingReminderNotificationId":99354},"successToastText":"Recorded 4 out of 5","text":"4\\/5","title":"4\\/5","tooltip":"Rate 4\\/5"}]',
            color: "#2196F3",
            "content-available": "1",
            "force-start": "1",
            foreground: "false",
            icon: "https://web.quantimo.do/img/variable_categories/emotions.png",
            image: "",
            isBackground: "true",
            lastValue: "3",
            message: "Pull down and select a value to record or tap to open inbox for more options",
            notId: "1398",
            secondToLastValue: "2",
            soundName: "false",
            thirdToLastValue: "4",
            title: "Track Overall Mood",
            trackingReminderNotificationId: "40611535",
            unitAbbreviatedName: "/5",
            url: "https://web.quantimo.do/#/app/reminders-inbox",
            valence: "positive",
            variableCategoryId: "1",
            variableDisplayName: "Overall Mood",
            variableName: "Overall Mood",
        }
        var notificationOptions = qm.notifications.convertPushDataToWebNotificationOptions(pushData, qm.getAppSettings())
        qm.assert.equals(3, notificationOptions.actions.length)
        qm.assert.equals("Overall Mood", notificationOptions.title)
        done()
    })
    it('can parse correlation push notification', function(done) {
        var pushData = {
            color: "#2196F3",
            "content-available": "1",
            "force-start": "0",
            forceStart: "0",
            foreground: "false",
            image: "https://web.quantimo.do/img/variable_categories/symptoms.png",
            isBackground: "true",
            message: "Your EffectVariableName is generally 40% higher after $1.1 over the previous 30 days. ",
            notId: "100624100625",
            soundName: "false",
            title: "↑Higher Purchases Of CauseVariableName Predicts Significantly ↑Higher EffectVariableName",
            url: "https://web.quantimo.do/#/app/study?causeVariableId=100624&effectVariableId=100625&userId=1&clientId=oauth_test_client",
            user: "1",
        }
        var notificationOptions = qm.notifications.convertPushDataToWebNotificationOptions(pushData, qm.getAppSettings())
        qm.assert.equals(notificationOptions.title, pushData.title)
        qm.assert.equals(notificationOptions.body, pushData.message)
        done()
    })
})
describe("Menu", function () {
    it("can move menu item down", function (done) {
        var original = JSON.parse(JSON.stringify(qm.menu.getMenu()))
        var reordered = qm.menu.moveMenuItemDown(JSON.parse(JSON.stringify(original)), 0)
        qm.assert.doesNotEqual(original[0].id, reordered[0].id)
        qm.assert.doesNotEqual(original[1].id, reordered[1].id)
        qm.assert.equals(original[0].id, reordered[1].id)
        done()
    })
    it("can move first menu item up", function (done) {
        var original = JSON.parse(JSON.stringify(qm.menu.getMenu()))
        var reordered = qm.menu.moveMenuItemUp(JSON.parse(JSON.stringify(original)), 0)
        qm.assert.equals(original[0].id, reordered[0].id)
        done()
    })
    it("can move menu item up", function (done) {
        var original = JSON.parse(JSON.stringify(qm.menu.getMenu()))
        var reordered = qm.menu.moveMenuItemUp(JSON.parse(JSON.stringify(original)), 1)
        qm.assert.equals(original[1].id, reordered[0].id)
        qm.assert.doesNotEqual(original[0].id, reordered[0].id)
        qm.assert.doesNotEqual(original[1].id, reordered[1].id)
        done()
    })
    it("can change variable category", function (done) {
        var before = {
            "stateName": "app.historyAllCategory",
            "href": "#/app/history-all-category/Physical+Activity",
            "url": "/history-all-category/:variableCategoryName",
            "icon": "ion-ios-body-outline",
            "subMenu": null,
            "params": {
                "showAds": true,
                "variableCategoryName": null,
                "refresh": null,
                "title": "History",
                "ionIcon": "ion-ios-list-outline",
            },
            "title": "Activity History",
            "id": "history-all-category-physical-activity",
            "showSubMenu": true,
            "$$hashKey": "object:3482",
            "cache": true,
            "views": {
                "menuContent": {
                    "templateUrl": "templates/history-all.html",
                    "controller": "historyAllMeasurementsCtrl",
                },
            },
            "name": "app.historyAllCategory",
        }
        before.params.variableCategoryName = "Nutrients"
        var updated = qm.menu.onParameterChange(JSON.parse(JSON.stringify(before)))
        qm.assert.contains("Nutrients", updated.href)
        qm.assert.equals("history-all-category-nutrients", updated.id)
        qm.assert.equals("Nutrients History", updated.title)
        qm.assert.doesNotContain(":variableCategory", updated.href)
        done()
    })
    it("can change state", function (done) {
        var before = {
            "stateName": "app.historyAllCategory",
            "href": "#/app/history-all-category/Physical+Activity",
            "url": "/history-all-category/:variableCategoryName",
            "icon": "ion-ios-body-outline",
            "subMenu": null,
            "params": {
                "showAds": true,
                "variableCategoryName": null,
                "refresh": null,
                "title": "History",
                "ionIcon": "ion-ios-list-outline",
            },
            "title": "Activity History",
            "id": "history-all-category-physical-activity",
            "showSubMenu": true,
            "$$hashKey": "object:3482",
            "cache": true,
            "views": {
                "menuContent": {
                    "templateUrl": "templates/history-all.html",
                    "controller": "historyAllMeasurementsCtrl",
                },
            },
            "name": "app.historyAllCategory",
        }
        before.stateName = qm.staticData.stateNames.charts
        var updated = qm.menu.onStateChange(JSON.parse(JSON.stringify(before)))
        qm.assert.contains("charts", updated.href)
        qm.assert.contains("charts", updated.id)
        qm.assert.equals("Charts", updated.title)
        qm.assert.doesNotContain(":variableCategory", updated.href)
        updated.params.variableName = "Overall Mood"
        updated = qm.menu.onStateChange(JSON.parse(JSON.stringify(before)))
        qm.assert.contains("Overall", updated.href)
        qm.assert.equals("charts-overall-mood", updated.id)
        qm.assert.equals("Overall Mood Charts", updated.title)
        qm.assert.doesNotContain(":variableName", updated.href)
        qm.assert.doesNotHaveProperty(updated.params, 'variableCategoryName')
        done()
    })
})
function createReminder(tr, expectedVariables, expectedNotifications, expectedReminders) {
    var queueBefore = qm.reminderHelper.getQueue()
    qm.reminderHelper.addToQueue([tr])
    expect(qm.reminderHelper.getQueue()).length(queueBefore.length + 1)
    return qm.reminderHelper.syncReminders()
        .then(function (response) {
            const data = (response && response.data) ? response.data : null
            expect(data.trackingReminders).length(expectedReminders)
            expect(data.userVariables).length(expectedVariables)
            expect(data.trackingReminderNotifications).length(expectedNotifications)
            expect(qm.notifications.getCached()).length(expectedNotifications)
            expect(qm.reminderHelper.getCached()).length(expectedReminders)
        }).catch(function(err){
            throw Error(err)
        })
}
describe("Test Helper", function () {
    it('can save to fixtures', function() {
        var method = "GET"
        var url = "https://test-url.com/some-path?param=hi"
        var data = {"code": 200, "data": {"param": "hi"}}
        var path = qm.tests.urlToFixturePath(method, url)
        return qm.tests.deleteFixture(method, url)
            .then(function (){
                fileHelper.assertDoesNotExist(path)
                qm.tests.addToFixture(method, url, data)
                var gotten = qm.tests.getFixtureData(method, url)
                expect(gotten).to.deep.eq(data)
            })
    })
})
describe("Time", function () {
    it('can convert to unix time', function () {
        var startAt = "2020-12-01 15:00:00"
        var millis = qm.timeHelper.getUnixTimestampInMilliseconds(startAt)
        expect(millis).to.eq(1606834800000)
        var unixTime = qm.timeHelper.getUnixTimestampInSeconds(startAt)
        expect(unixTime).to.eq(1606834800)
        unixTime = qm.timeHelper.universalConversionToUnixTimeSeconds(startAt)
        expect(unixTime).to.eq(1606834800)
        expect(qm.timeHelper.toMySQLTimestamp(startAt)).to.eq(startAt)
    })
})
describe("Units", function () {
    it('can get units', function(done) {
        var units = qm.unitHelper.getAllUnits()
        qmLog.debug("units:", units)
        qm.assert.greaterThan(5, units.length)
        done()
    })
})
describe("URL Helper", function () {
    it('can get query params before hash', function(done) {
        var url = 'https://dev-web.quantimo.do/?clientId=preve-wellness-tracker#/app/onboarding'
        var params = qm.urlHelper.getQueryParams(url)
        qm.assert.equals('preve-wellness-tracker', params.clientId)
        done()
    })
})

function generateRandomEmail() {
    let rand = Math.round(Math.random() * 1000000);
    var email = "testuser" + rand + "@quantimo.do";
    return email;
}

describe("Users", function () {
    it('can login via email and password via function', function(done) {
        this.timeout(10000)
        let request = { body: { email: "testuser@mikesinn.com", password: "testing123" } };
	    connectionHelper.loginViaEmail(request,
          function(something, user, err){
            if(err){throw Error(err)}
            qmLog.debug("user:", user)
            qm.assert.equals("testuser", user.user_login);
            done()
        })
    })
    it('can create a user via Prisma', function(done) {
        this.timeout(10000)
        var email = generateRandomEmail();
        var plainTextPassword = "testing123";
        var salt = connectionHelper.generateSalt();
	    connectionHelper.hashPassword(plainTextPassword, salt, function(err, hashedPassword) {
                if (err) { throw err; }
                db.createUser({
                    email: email,
                    salt: salt,
                    password: hashedPassword
                }).then(function(user) {
                    qmLog.debug("user:", user)
                    qm.assert.equals(email, user.email)
                    qm.assert.notNull(user.id, 'user.id')
                    connectionHelper.loginViaEmail({
                        body: { email: email, password: plainTextPassword }
                    }, function (err, user) {
                        if(err) { throw err; }
                        qm.assert.equals(email, user.email)
                        done(err, user);
                    });
                })
            });
    })
})
describe("Login", function () {
    it('can get user by connector user id', function(done) {
        this.timeout(20000)
        let prisma = db.prisma;
        let connections = prisma.connections;
        connections.findFirst({where: {NOT:{connector_user_id: null}}})
            .then(function(connection){
                connectionHelper.findUserByConnectorUserId('googleplus', {id: connection.connector_user_id})
                    .then(function(user){
                        expect(user).to.have.property('ID')
                        done()
                    })
            })
    });
})
const digitalTwinApi = require('../../public/js/digitalTwinApi')
describe("NFT", function () {
	it('can generate an life-force image', function(done) {
		this.timeout(20000)
		qm.auth.setAccessToken('demo')
		const img = digitalTwinApi.generateLifeForceNftImage();
		img.then(function(img){
			qmLog.debug(img)
			expect(img).to.be.a('string')
			
			done()
		})
	});
	it('can generate an variable image', function(done) {
		this.timeout(20000)
		qm.auth.setAccessToken('demo')
		const img = digitalTwinApi.mintNFTForUserVariable();
		img.then(function(img){
			qmLog.debug(img)
			expect(img).to.be.a('string')
			done()
		})
	});
})
