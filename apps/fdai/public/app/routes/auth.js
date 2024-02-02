let express = require('express');
let passport = require('passport');
let LocalStrategy = require('passport-local');
let qm = require('../public/js/qmHelpers.js');
let qmLog = require('../public/js/qmLogger.js');
const credentials = require("../utils/credentials");
const authHelper = require("../utils/authHelper");
const connectionHelper = require("../utils/connectionHelper");
const urlHelper = require("../utils/urlHelper");
const db = require("../db");
const { getAccessTokenFromRequest, findUserByAccessToken, setUserInSession, loginViaEmail } = require("../utils/authHelper");
const { createAccessToken } = require("../db");


  /* Configure session management.
   *
   * When a login session is established, information about the user will be
   * stored in the session.  This information is supplied by the `serializeUser`
   * function, which is yielding the user ID and username.
   *
   * As the user interacts with the app, subsequent requests will be authenticated
   * by verifying the session.  The same user information that was serialized at
   * session establishment will be restored when the session is authenticated by
   * the `deserializeUser` function.
   */

passport.serializeUser( (user, done) => {
  // The USER object is the "authenticated user" from the done() in authUser function.
  // serializeUser() will attach this user to "req.session.passport.user.{user}", so that it is tied to the session object for each session.
    let serializedUser = qm.userHelper.serializeUser(user);
    console.log(`\n--------> Serialized User:`)
    console.log(user)
  done(null, serializedUser)
} )


passport.deserializeUser((user, done) => {
    let logIt = false;
  if(logIt && qm.appMode.isDebug()){
    console.log("\n--------- Deserialized User:")
    console.log(user)
  }
  // This is the {user} that was saved in req.session.passport.user.{user} in the serializationUser()
  // deserializeUser will attach this {user} to the "req.user.{user}", so that it can be used anywhere in the App.

  done (null, user)
})


let router = express.Router();

/* GET /login
 *
 * This route prompts the user to log in.
 *
 * The 'login' view renders an HTML form, into which the user enters their
 * username and password.  When the user submits the form, a request will be
 * sent to the `POST /login/password` route.
 */
let handleGetLogin = function(req, res, next) {
  //res.render('login');
  return res.redirect("/#/app/login")
};
router.get('/login', handleGetLogin);
router.get('/auth/login', handleGetLogin);
/* Configure password authentication strategy.
 *
 * The `LocalStrategy` authenticates users by verifying a username and password.
 * The strategy parses the username and password from the request and calls the
 * `verify` function.
 *
 * The `verify` function queries the database for the user record and verifies
 * the password by hashing the password supplied by the user and comparing it to
 * the hashed password stored in the database.  If the comparison succeeds, the
 * user is authenticated; otherwise, not.
 */
passport.use(new LocalStrategy(
    {
        usernameField: 'email',
        passReqToCallback: true
    }, function(req, email, password, done) {
        return authHelper.loginViaEmail(req, async function (err, user) {
            await addAccessTokenToUser(user, req, done);
            done(err, user);
        });
    }));
/* POST /login/password
 *
 * This route authenticates the user by verifying a username and password.
 *
 * A username and password are submitted to this route via an HTML form, which
 * was rendered by the `GET /login` route.  The username and password is
 * authenticated using the `local` strategy.  The strategy will parse the
 * username and password from the request and call the `verify` function.
 *
 * Upon successful authentication, a login session will be established.  As the
 * user interacts with the app, by clicking links and submitting forms, the
 * subsequent requests will be authenticated by verifying the session.
 *
 * When authentication fails, the user will be re-prompted to login and shown
 * a message informing them of what went wrong.
 */
// router.post('/login/password', passport.authenticate('local', {
//   successReturnToOrRedirect: urlHelper.loginSuccessRedirect,
//   failureRedirect: urlHelper.loginFailureRedirect,
//   failureMessage: true
// }));
let emailLoginHandler = async function(req, res, next) {
	authHelper.loginViaEmail(req, async function (err, dbUser) {
		if(err){
			return next(err);
		}
		await addAccessTokenToUser(dbUser, req, function(err, dbUser){
			if(err){
				return next(err);
			}
			return res.json(dbUser).status(201);
		});
	});
}
router.post('/login/password', emailLoginHandler);
router.post('/auth/login', emailLoginHandler);


/* POST /signup
 *
 * This route creates a new user account.
 *
 * A desired username and password are submitted to this route via an HTML form,
 * which was rendered by the `GET /signup` route.  The password is hashed and
 * then a new user record is inserted into the database.  If the record is
 * successfully created, the user is logged in.
 */
let registerNewUser = async function(req, res, next) {

    let body = req.body;
    let email = body.email || body.log;
    if(body.password !== body.passwordConfirm){
        return res.status(400).send("Passwords do not match");
    }
    let user = await db.findUserByEmail(email);
    if(user){
        loginViaEmail(req, function (err, user) {
            if (err) {
                return res.status(400).send("Error logging in");
            }
            return res.status(201).json(user);
        });
    }
    let salt = authHelper.generateSalt();
    authHelper.hashPassword(req.body.password, salt, function(err, hashedPassword) {
        if (err) { return next(err); }
        db.createUser({
            email: email,
            salt: salt,
            password: hashedPassword
        }).then(function(user) {
            addAccessTokenToUser(user, req, function(err, user) {
                req.login(user, function(err) {
                    if (err) { return next(err); }
                    //res.redirect('/');
                    return res.status(201).json(user);
                });
            });
        }).catch(function(err) {
            return next(err);
        });
    });
};
router.post('/auth/register', registerNewUser);
router.post('/signup', registerNewUser);
let addAccessTokenToUser = function(dbUser, request, done){
    return db.createAccessToken(dbUser).then((token) => {
        dbUser.accessToken = token.access_token;
        authHelper.setUserInSession(request, dbUser);
        qm.userHelper.setUser(dbUser);
        return done(null, dbUser);
    });
}
function logout(req, res, next){
    req.session.access_token = null;
    req.session.refresh_token = null;
    req.session.user = null;
    req.user = null;
}
function logoutAndRedirect(req, res, next){
    logout(req, res, next);
    return res.redirect('/');
}
/* POST /logout
 *
 * This route logs the user out.
 */
router.post('/auth/logout', function(req, res, next) {
  req.logout(function(err) {
    if (err) { return next(err); }
    logout(req, res, next);
  });
});
let getLogoutHandler = function(req, res, next) {
  req.logout(function(err) {
    if (err) { return next(err); }
    logoutAndRedirect(req, res, next);
  });
    logoutAndRedirect(req, res, next);
};

/* GET /auth/logout
 *
 * This route logs the user out.
 */
router.get('/auth/logout', getLogoutHandler);

/* GET /auth/logout
 *
 * This route logs the user out.
 */

router.get('/logout', getLogoutHandler);

function socialLoginRoutes(strategyName, Strategy, connectorName) {
  if(!connectorName){connectorName = strategyName;}
    const authOrigin = process.env.AUTH_ORIGIN || "https://auth.quantimo.do";
    let callbackPath = "/auth/" + strategyName + "/callback";
    let redirect_uri = authOrigin + callbackPath;
    let authOpts = {
        scope: credentials.getScopes(connectorName),
        redirect_uri: redirect_uri
    };
	let authPath = "/auth/" + connectorName
	if(qm.urlHelper.getOnAuthDomain()){
        qmLog.info("ON AuthDomain so handleConnection locally");
        let strategyOpts = credentials.find(strategyName, connectorName);
        try {
            passport.use(new Strategy(strategyOpts, function(request, accessToken, refreshToken, profile, done){
                return connectionHelper.handleConnection(request, accessToken, refreshToken, profile, done, connectorName);
            }));
        } catch (e) {
            qmLog.error("Error creating " + strategyName + " strategy.  Please check your credentials.json file.  Error: " + e);
            throw e
        }
        router.get(authPath, passport.authenticate(strategyName, authOpts));
    } else {
        qmLog.info("NOT ON AuthDomain so adding redirect to auth url: " + authOrigin);
        router.get(authPath, function(req, res, next){
            // TODO: Keep token serverside and just use cookies
            // const windowLocationCallback = req.query.final_callback_url;
            // req.session.windowLocationCallback = windowLocationCallback;
            // const tokenStorageCallback = process.env.EXPRESS_ORIGIN + QM_TOKEN_CALLBACK_PATH;
            // return res.redirect(authOrigin + "/auth/" + connectorName+ "?final_callback_url="
            //     + encodeURIComponent(tokenStorageCallback));
            const finalCallback = req.query.final_callback_url;
            let redirectUrl = authOrigin + authPath+ "?final_callback_url="
                + encodeURIComponent(finalCallback);
            return res.redirect(redirectUrl);
        });
    }

  router.get(callbackPath, passport.authenticate(strategyName, {
      //successRedirect: urlHelper.loginSuccessRedirect,
      failureRedirect: urlHelper.loginFailureRedirect
    }), (req, res) => {
        let user = req.user;
        let currentUrl = urlHelper.getUrlFromReq(req);
      let finalCallbackUrl = req.session.final_callback_url;
      if(finalCallbackUrl){
          let t = getAccessTokenFromRequest(req);
          req.session.final_callback_url = null;
          if(t){
              finalCallbackUrl = qm.urlHelper.addUrlQueryParamsToUrlString({
                  logout: 0,
                  quantimodoAccessToken: t,
              }, finalCallbackUrl);
              res.redirect(finalCallbackUrl);
          } else {
              createAccessToken(user).then(function(tokenObj){
                  t = tokenObj.access_token;
                  finalCallbackUrl = qm.urlHelper.addUrlQueryParamsToUrlString({
                      logout: 0,
                      quantimodoAccessToken: t,
                  }, finalCallbackUrl);
                  res.redirect(finalCallbackUrl);
              });
          }
      } else {
        res.redirect(urlHelper.loginSuccessRedirect);
      }
    });
}
socialLoginRoutes('facebook', require('passport-facebook').Strategy);
socialLoginRoutes('fitbit', require('passport-fitbit-oauth2').FitbitOAuth2Strategy);
socialLoginRoutes('github', require('passport-github').Strategy);
socialLoginRoutes('google', require('passport-google-oauth2').Strategy, 'googlefit');
socialLoginRoutes('google', require('passport-google-oauth2').Strategy, 'googleplus');
socialLoginRoutes('linkedin', require('passport-linkedin-oauth2').Strategy);
socialLoginRoutes('netatmo', require('passport-netatmo').Strategy);
socialLoginRoutes('rescuetime', require('passport-rescuetime').Strategy);
socialLoginRoutes('runkeeper', require('passport-runkeeper').Strategy);
socialLoginRoutes('slack', require('passport-slack').Strategy);
socialLoginRoutes('twitter', require('passport-twitter').Strategy);
socialLoginRoutes('withings', require('passport-withings').Strategy);
socialLoginRoutes('quantimodo', require('./passport-quantimodo-oauth2/lib/index').Strategy);

module.exports = router;
