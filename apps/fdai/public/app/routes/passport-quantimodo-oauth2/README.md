# Passport strategy for QuantiModo OAuth 2.0

[Passport](http://passportjs.org/) strategies for authenticating with [QuantiModo](http://quantimo.do)
using ONLY OAuth 2.0.

This module lets you authenticate using QuantiModo in your Node.js [Express](http://expressjs.com/) (or [Connect](http://www.senchalabs.org/connect/)) server applications. 


## Install

    $ npm install passport-quantimodo-oauth2

## Usage of OAuth 2.0

#### Configure Strategy

The QuantiModo OAuth 2.0 authentication strategy requires a `verify` callback, which
accepts these credentials and calls `done` providing a user, as well as
`options` specifying a client ID, client secret, and callback URL.

```
var QuantiModoStrategy = require( 'passport-quantimodo-oauth2' ).Strategy;;

passport.use(new QuantiModoStrategy({
    clientID:     QUANTIMODO_CLIENT_ID,
    clientSecret: QUANTIMODO_CLIENT_SECRET,
    callbackURL: "http://yourdormain:3000/auth/quantimodo/callback"
  },
  function(accessToken, refreshToken, profile, done) {
    User.findOrCreate({ quantimodoId: profile.id }, function (err, user) {
      return done(err, user);
    });
  }
));
```

#### Authenticate Requests

Use `passport.authenticate()`, specifying the `'quantimodo'` strategy, to
authenticate requests.

For example, as route middleware in an [Express](http://expressjs.com/)
application:

```
app.get('/auth/quantimodo',
  passport.authenticate('quantimodo', { scope: ['readmeasurements','writemeasurements'] }
));

app.get( '/auth/quantimodo/callback', passport.authenticate( 'quantimodo', { 
        successRedirect: '/auth/quantimodo/success',
        failureRedirect: '/auth/quantimodo/failure'
}));
```
