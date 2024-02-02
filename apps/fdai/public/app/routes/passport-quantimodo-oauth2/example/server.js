var express = require('express')
  , passport = require('passport')
  , QuantiModoStrategy = require('../lib').Strategy;

var logger = require('morgan');
var envHelper = require('../../../ts/env-helper');
envHelper.loadEnvFromDopplerOrDotEnv(null);

// API Access link for creating client ID and secret:
// https://www.quantimodo.com/secure/developer
var QUANTIMODO_CLIENT_ID = process.env.QUANTIMODO_CLIENT_ID || process.env.CONNECTOR_QUANTIMODO_CLIENT_ID;
var QUANTIMODO_CLIENT_SECRET = process.env.QUANTIMODO_CLIENT_SECRET || process.env.CONNECTOR_QUANTIMODO_CLIENT_SECRET;
var CALLBACK_URL = process.env.CALLBACK_URL || 'http://localhost:3000/auth/quantimodo/callback';


// Passport session setup.
//   To support persistent login sessions, Passport needs to be able to
//   serialize users into and deserialize users out of the session.  Typically,
//   this will be as simple as storing the user ID when serializing, and finding
//   the user by ID when deserializing.  However, since this example does not
//   have a database of user records, the complete QuantiModo profile is
//   serialized and deserialized.
passport.serializeUser(function(user, done) {
  done(null, user);
});

passport.deserializeUser(function(obj, done) {
  done(null, obj);
});


// Use the QuantiModoStrategy within Passport.
//   Strategies in Passport require a `verify` function, which accept
//   credentials (in this case, an accessToken, refreshToken, and QuantiModo
//   profile), and invoke a callback with a user object.
passport.use(new QuantiModoStrategy({
    clientID:     QUANTIMODO_CLIENT_ID,
    clientSecret: QUANTIMODO_CLIENT_SECRET,
    callbackURL:  CALLBACK_URL,
    scope:        ['readmeasurements', 'writemeasurements'],
    passReqToCallback: true
  },
  function(req, accessToken, refreshToken, profile, done) {
    // asynchronous verification, for effect...
    req.session.accessToken = accessToken;
    process.nextTick(function () {
      // To keep the example simple, the user's QuantiModo profile is returned to
      // represent the logged-in user.  In a typical application, you would want
      // to associate the QuantiModo account with a user record in your database,
      // and return that user instead.
      return done(null, profile);
    });
  }
));




var app = express();
// configure Express
app.set('views', __dirname + '/views');
app.set('view engine', 'ejs');
app.use(logger('dev'));
var cookieSession = require('cookie-session')
app.use(require('cookie-parser')());
app.set('trust proxy', 1) // trust first proxy
app.use(cookieSession({
	name: 'session',
	keys: [process.env.JWT_SECRET],
	maxAge: 24 * 60 * 60 * 1000 * 30 // 30 days
}))
app.use(passport.initialize());
app.use(passport.session());
app.use(express.urlencoded());
app.use(express.json());
// Initialize Passport!  Also use passport.session() middleware, to support
// persistent login sessions (recommended).
app.use(passport.initialize());
app.use(passport.session());
//app.use(app.router);
app.use(express.static(__dirname + '/public'));


app.get('/', function(req, res){
  res.render('index', { user: req.user });
});

app.get('/account', ensureAuthenticated, function(req, res){
  res.render('account', { user: req.user });
});

// GET /auth/quantimodo
//   Use passport.authenticate() as route middleware to authenticate the
//   request.  The first step in QuantiModo authentication will involve
//   redirecting the user to quantimodo.com.  After authorization, QuantiModo
//   will redirect the user back to this application at /auth/quantimodo/callback
app.get('/auth/quantimodo',
  passport.authenticate('quantimodo', { state: 'SOME STATE' }),
  function(req, res){
    // The request will be redirected to QuantiModo for authentication, so this
    // function will not be called.
  });

// GET /auth/quantimodo/callback
//   Use passport.authenticate() as route middleware to authenticate the
//   request.  If authentication fails, the user will be redirected back to the
//   login page.  Otherwise, the primary route function will be called,
//   which, in this example, will redirect the user to the home page.
app.get('/auth/quantimodo/callback',
  passport.authenticate('quantimodo', { failureRedirect: '/login' }),
  function(req, res) {
    res.redirect('/');
  });

app.get('/logout', function(req, res){
  req.logout();
  res.redirect('/');
});

var http = require('http');
http.createServer(app).listen(3000);


// Simple route middleware to ensure user is authenticated.
//   Use this route middleware on any resource that needs to be protected.  If
//   the request is authenticated (typically via a persistent login session),
//   the request will proceed.  Otherwise, the user will be redirected to the
//   login page.
function ensureAuthenticated(req, res, next) {
  if (req.isAuthenticated()) { return next(); }
  res.redirect('/login');
}
