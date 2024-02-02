var express = require('express'),
    app = express(),
    bodyParser = require('body-parser'),
    morgan = require('morgan'),
    path = require('path');
const { initialize } = require('@oas-tools/core');
const proxy = require('express-http-proxy');

var Bugsnag = require('@bugsnag/js')
var BugsnagPluginExpress = require('@bugsnag/plugin-express')
const http = require("http");
Bugsnag.start({
    apiKey: process.env.BUGSNAG_API_KEY || "5b0414a9a476d93d154fa294c76ac6ed",
    plugins: [BugsnagPluginExpress],
})
var middleware = Bugsnag.getPlugin('express')
// This must be the first piece of middleware in the stack.
// It can only capture errors in downstream middleware
app.use(middleware.requestHandler)
// This handles any errors that Express catches. This needs to go before other
// error handlers. Bugsnag will call the `next` error handler if it exists.
app.use(middleware.errorHandler)
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(morgan('dev'));

// CORS (Cross-Origin Resource Sharing) headers to support Cross-site HTTP requests
app.use(function(req, res, next) {
    // Don't allow cross-origin to prevent usage of client id and secret
    // res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST');
    res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With, content-type, Authorization, X-Client-ID, X-Client-Secret');
    next();
});

const apiUrl = process.env.API_URL || 'https://app.quantimo.do/api';
const serverPort = 8080 || process.env.PORT;

app.use('/', proxy(apiUrl));
//app.use('/api', proxy(apiUrl+'/api'));

app.use(express.json({limit: '50mb'}));

const config = {
    middleware: {
        security: {
            auth: {
                access_token: () => { /* no-op */ },
                bearerAuth: () => {  },
                client_id: () => { /* no-op */ },
                curedao_oauth2: () => { /* no-op */ },
            }
        }
    }
}


initialize(app, config).then(() => {
    http.createServer(app).listen(serverPort, () => {
        console.log("\nApp running at "+apiUrl+":" + serverPort);
        console.log("________________________________________________________________");
        if (config.middleware.swagger?.disable !== false) {
            console.log("API docs (Swagger UI) available on "+apiUrl+":" + serverPort + '/docs');
            console.log("________________________________________________________________");
        }
    });
});
