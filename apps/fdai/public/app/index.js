var express = require('express'),
    app = express(),
    bodyParser = require('body-parser'),
    logger = require('morgan');
const path = require('path');
const envHelper = require("./ts/env-helper");
if(!process.env.EXPRESS_ORIGIN){
    envHelper.loadEnvFromDopplerOrDotEnv(null)
}
envHelper.getRequiredEnv('DATABASE_URL');
let bugsnagMiddleware = false
if(process.env.BUGSNAG_API_KEY){
    var Bugsnag = require('@bugsnag/js')
    var BugsnagPluginExpress = require('@bugsnag/plugin-express')
    Bugsnag.start({
        apiKey: process.env.BUGSNAG_API_KEY,
        plugins: [BugsnagPluginExpress],
        otherOptions: {
            releaseStage: process.env.NODE_ENV,
            appVersion: process.env.npm_package_version,
            appType: "web_server"
        }
    })
//Bugsnag.notify({ name: "Request URL", message: urlHelper.getFullUrl(req) })
    global.bugsnagClient = Bugsnag;
    bugsnagMiddleware = Bugsnag.getPlugin('express')
    app.use(bugsnagMiddleware.requestHandler)
}
app.use(logger('dev'));
const http = require("http");
const qm = require("./public/js/qmHelpers");
global.fetch = require('node-fetch');
const qmLog = require("./public/js/qmLogger");
if(qm.edgeConfig.available()){
    qm.edgeConfig.get("authDebug").then((authDebug) => {
        qmLog.setAuthDebugEnabled(authDebug)
    })
}
const urlHelper = require("./utils/urlHelper");
const authHelper = require("./utils/authHelper");
const passport = require('passport')
global.Q = require('q');
var cookieSession = require('cookie-session')
app.use(require('cookie-parser')());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.set('trust proxy', 1) // trust first proxy
app.use(cookieSession({
    name: 'session',
    keys: [process.env.JWT_SECRET],
    maxAge: 24 * 60 * 60 * 1000 * 30 // 30 days
}))
app.use(passport.initialize());
app.use(passport.session());
app.get('/cookie', function (req, res, next) {
    // Update views
    req.session.views = (req.session.views || 0) + 1
    // Write response
    res.end(req.session.views + ' views \nsession: '+
        JSON.stringify(req.session, null, 2))
})
app.use((req, res, next) => {
    if(!qm.fileHelper.isStaticAsset(req.url)){

        qmLog.debug("\n==============================")
        qmLog.info("req", req.url)
        qmLog.debug(`req.session.passport --> `,req.session.passport)
        qmLog.debug(`req.user -> `,req.user)
        qmLog.debug(`req.session.id -> ${req.session.id}`)
        qmLog.debug(`req.session.cookie --> `,req.session.cookie)
        qmLog.debug("===========================================\n")
    }
    next()
})
app.use(authHelper.addAccessTokenToSession)

// CORS (Cross-Origin Resource Sharing) headers to support Cross-site HTTP requests
app.use(function(req, res, next) {
    // Don't allow cross-origin to prevent usage of client id and secret
    // res.setHeader('Access-Control-Allow-Origin', '*');
    if(!qm.fileHelper.isStaticAsset(req.url)){
        qm.request = req;
        qm.response = res;
        res.setHeader('Access-Control-Allow-Methods', 'GET, POST');
        res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With, content-type, Authorization, X-Client-ID, X-Client-Secret');
    }
    next();
});
app.use('/', express.static(path.join(__dirname, 'public')))
app.use('/#app', express.static(path.join(__dirname, 'public')))
//app.use('/docs', express.static(path.join(__dirname, 'public/docs')))
//app.use('/data', express.static(path.join(__dirname, 'public/data')))
//app.use('/js', express.static(path.join(__dirname, 'public/js')))
app.use('/', require('./routes/api'));
app.use('/', require('./routes/auth'));
//app.use('/', require('./routes/github'));
var server = http.createServer(app);
server.listen(urlHelper.serverPort);
console.info('Server running at ' + urlHelper.serverOrigin);
if(bugsnagMiddleware){app.use(bugsnagMiddleware.errorHandler)}
server.on('error', (e) => {
    if (e.code === 'EADDRINUSE') {
        console.error('Address in use, retrying...');
    } else {
        console.error(e);
    }
})
module.exports = app // Export the app for it to be run as a serverless function.
