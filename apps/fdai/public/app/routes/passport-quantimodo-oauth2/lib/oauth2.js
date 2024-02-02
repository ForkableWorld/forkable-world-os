/**
 * Module dependencies.
 */
var util = require("util")
	, OAuth2Strategy = require("passport-oauth2")
	, InternalOAuthError = require("passport-oauth2").InternalOAuthError
/**
 * `Strategy` constructor.
 *
 * The QuantiModo authentication strategy authenticates requests by delegating to
 * QuantiModo using the OAuth 2.0 protocol.
 *
 * Applications must supply a `verify` callback which accepts an `accessToken`,
 * `refreshToken` and service-specific `profile`, and then calls the `done`
 * callback supplying a `user`, which should be set to `false` if the
 * credentials are not valid.  If an exception occured, `err` should be set.
 *
 * Options:
 *   - `clientID`      your QuantiModo application's client id
 *   - `clientSecret`  your QuantiModo application's client secret
 *   - `callbackURL`   URL to which QuantiModo will redirect the user after granting authorization
 *
 * Examples:
 *
 *     passport.use(new QuantiModoStrategy({
 *         clientID: '123-456-789',
 *         clientSecret: 'shhh-its-a-secret'
 *         callbackURL: 'https://www.example.net/auth/quantimodo/callback'
 *       },
 *       function(accessToken, refreshToken, profile, done) {
 *         User.findOrCreate(..., function (err, user) {
 *           done(err, user);
 *         });
 *       }
 *     ));
 *
 * @param {Object} options
 * @param {Function} verify
 * @api public
 */
function Strategy(options, verify){
	options = options || {}
	let qmApiOrigin = qm.api.getQMApiOrigin()
	options.authorizationURL = options.authorizationURL || qmApiOrigin + "/oauth/authorize"
	options.tokenURL = options.tokenURL || qmApiOrigin + "/oauth/token"
	options.scopeSeparator = options.scopeSeparator || " "
	options.clientId = options.clientId || options.clientID || qm.getClientId()
	options.clientSecret = options.clientSecret || qm.api.getClientSecret()
	// options.customHeaders = {
	//   Authorization:  'Basic '+ new Buffer(options.clientID + ':' + options.clientSecret).toString('base64')
	// };
	OAuth2Strategy.call(this, options, verify)
	this.name = "quantimodo"
}
/**
 * Inherit from `OAuth2Strategy`.
 */
util.inherits(Strategy, OAuth2Strategy)
Strategy.prototype.authenticate = function(req, options){
	options || (options = {})
	OAuth2Strategy.prototype.authenticate.call(this, req, options)
}
/**
 * Retrieve user profile from QuantiModo.
 *
 * This function constructs a normalized profile, with the following properties:
 *
 *   - `provider`         always set to `quantimodo`
 *   - `id`
 *   - `name`
 *   - `displayName`
 *   - `birthday`
 *   - `relationship`
 *   - `isPerson`
 *   - `isPlusUser`
 *   - `placesLived`
 *   - `language`
 *   - `emails`
 *   - `gender`
 *   - `picture`
 *
 * @param {String} accessToken
 * @param {Function} done
 * @api protected
 */
Strategy.prototype.userProfile = function(accessToken, done){
	this._oauth2.useAuthorizationHeaderforGET(true)
	let qmApiOrigin = qm.api.getQMApiOrigin()
	let url = qmApiOrigin + "/api/v1/user"
	this._oauth2.get(url, accessToken, function(err, body, res){
		if(err){ return done(new InternalOAuthError("failed to fetch user profile", err)) }
		try {
			var profile = JSON.parse(body)
			profile.provider = "quantimodo"
			profile.emails = [{value: profile.email}]
			profile._json = profile
			done(null, profile)
		} catch(e) {
			done(e)
		}
	})
}
/**
 * Return extra parameters to be included in the request token
 * request.
 *
 * @param {Object} options
 * @return {Object}
 * @api protected
 */
Strategy.prototype.authorizationParams = function(options){
	var params = options || {}
	var scope = options.scope
	if(scope){
		params["scope"] = Array.isArray(scope) ? scope.join(" ") : scope
	}
	return params
}
/**
 * Expose `Strategy` directly from package.
 */
exports = module.exports = Strategy
/**
 * Export constructors.
 */
exports.Strategy = Strategy
