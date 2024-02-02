const qm = require("../public/js/qmHelpers");
const fetch = require('node-fetch');
global.fetch = fetch
global.Headers = fetch.Headers;

module.exports.setUserInSession = function(request, user){
    if(request.session) {
        request.session.user = request.user = qm.userHelper.serializeUser(user);
    } else {
        qmLog.error("No session found in request");
    }
}

function getUserId(dbUser){
  //debugger
  return BigInt(dbUser.id || dbUser.ID || null);
}
module.exports.getIdFromUser = function(user){
  //debugger
  return getUserId(user);
}

module.exports.deleteAccessTokenFromRequest = function(req){
	qmLog.error("deleting Access Token From Request session...");
	if(req.session && req.session.access_token){
		let fromSession = req.session.access_token;
		delete req.session.access_token;
	}
}
function getAccessTokenFromRequest(req) {
  //debugger
  let fromSession, fromHeader, fromQuery, fromUser;
  if(req.session && req.session.access_token){fromSession = req.session.access_token;}
  const user = req.user;
  if(user){
    if(user.access_token && user.access_token.access_token){fromUser = user.access_token.access_token;}
    if(user.accessToken){fromUser = user.accessToken;}
  }
  const bearerHeader = req.headers['authorization'];
  if (bearerHeader && bearerHeader.startsWith('Bearer ')) {
    fromHeader = bearerHeader.replace('Bearer ', '');
  }
  let query = req.query;
  if(query && query.access_token){fromQuery = query.access_token;}
    if(query && query.accessToken){fromQuery = query.accessToken;}
    let fromRequest = fromQuery || fromHeader
    let fromSessionOrUser = fromSession || fromUser
  if(fromRequest && fromSessionOrUser && fromRequest !== fromSessionOrUser){
      req.session.access_token = req.user = req.session.user = null;
      qmLog.error("Access token from session does not match access token from header!  Using one from request.");
      return fromRequest;
  }
  qmLog.debug("getAccessTokenFromRequest: " +
      "fromSession: " + fromSession + ", " +
              "fromHeader: " + fromHeader + ", " +
              "fromQuery: " + fromQuery + ", " +
              "fromUser: " + fromUser);
  return fromQuery || fromHeader || fromUser || fromSession;
}
module.exports.getAccessTokenFromRequest = getAccessTokenFromRequest
module.exports.addAccessTokenToSession = function (req, res, next){
    if(!qm.fileHelper.isStaticAsset(req.url)){
        const accessToken = getAccessTokenFromRequest(req);
        if(accessToken){req.session.access_token = accessToken;}
        if(req.query.final_callback_url){req.session.final_callback_url = req.query.final_callback_url;}
    }
    next();
}
