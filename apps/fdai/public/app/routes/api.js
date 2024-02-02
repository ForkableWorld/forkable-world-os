var express = require('express');
const stringHelper = require("../utils/stringHelper");
const qm = require("../public/js/qmHelpers");
const authHelper = require("../utils/authHelper");
const https = require("https");
const digitalTwinApi  = require("../public/js/digitalTwinApi");
const connectionHelper = require("../utils/connectionHelper")

let unauthorizedResponse = {
  "error": "Unauthorized",
  "message": "You are not authorized to access this resource.",
  "status": 401
};
function handleUnauthorizedRequest(req, res){
  if(req.path.startsWith("/api")){
    return res.status(403).send("Forbidden");
  } else {
    return res.redirect("/login");
  }
}
//Use the req.isAuthenticated() function to check if user is Authenticated
const checkAuthenticated = async (req, res, next) => {
  //debugger
  let authorized = req.isAuthenticated();
  let user = req.user
  if(!user){
    let accessToken = authHelper.getAccessTokenFromRequest(req);
    if(accessToken){
      user = await connectionHelper.findUserByAccessToken(accessToken);
      if(user){req.session.user = req.user = user;}
    }
  }
  if(!user){
    return handleUnauthorizedRequest(req, res);
  }
  next();
}
function logRequest(req, providedMessage){
   var fullMessage = `\n==============================`
    fullMessage += `\n${providedMessage}`
   fullMessage += `\nreq.url --> ${req.url}`
    var user = req.user;
    if(user){
        //fullMessage += `\n user --> ${JSON.stringify(user, null, 2)}`
        fullMessage += `\nuser --> ${user.email}`
    } else {
        fullMessage += `\nNo user`
    }
    fullMessage += `\n==============================`
    console.log(fullMessage)
}

var router = express.Router();
router.get('/api/user', checkAuthenticated, async (req, res) => {
  if(!req.user){
    res.status(401).json(unauthorizedResponse);
    return;
  }
  res.status(200).json(req.user)
})

router.get('/api/digital-twin', checkAuthenticated, async (req, res) => {
	if(!req.user){
		res.status(401).json(unauthorizedResponse);
		return;
	}
	const canvas = await digitalTwinApi.generateLifeForceCanvas();
	let buffer = canvas.toBuffer();
	//res.contentType('image/png');
	//res.send(image);
	//create headers object
	const headers = { "Content-Type": "image/png" };

	//set status code and headers
	res.writeHead(200, headers);

	//end by sending image
	res.end(buffer);
	//res.send(Buffer.from(data, 'binary'))
	//res.status(200).json(req.user)
})

async function proxyRequestToQMAPI(req, res, body) {
    let path = req.path;
    let method = req.method;
    const agent = new https.Agent({
        rejectUnauthorized: process.env['PROXY_REJECT_UNAUTHORIZED'] || false,
    });
	if(qm.appMode.isDevelopment()){
		req.query.XDEBUG_SESSION_START = "PHPSTORM";
	}
    const init = {
        query: req.query,
        method: method,
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + authHelper.getAccessTokenFromRequest(req),
            'X-Client-ID': qm.getClientId(null, req)
        },
        agent
    };
    if(body){init.body = JSON.stringify(body);}
    let qmAPIUrl = qm.api.getQMApiOrigin() + path;
    qmAPIUrl = new URL(qmAPIUrl);
    Object.keys(req.query).forEach(key => qmAPIUrl.searchParams.append(key, req.query[key]))
    qmAPIUrl = qmAPIUrl.toString();
    console.log(`Proxying ${method} request to ${qmAPIUrl}`);
    var response = await fetch(qmAPIUrl, init);
	if(response.status === 401){
		authHelper.deleteAccessTokenFromRequest(req);
		return res.status(401).json(unauthorizedResponse);
	}
    if(response.status === 204){
        res.status(204).send(response.statusText);
        return;
    }
    var text = await response.text();
    var json = qm.stringHelper.isJson(text)
	let status = res.status(response.status)
	if(json){
        status.json(json);
    } else {
        status.send(text);
    }
}
router.get('/api/*', async function(req, res) {
    await proxyRequestToQMAPI(req, res);
});
router.post('/api/*', async function(req, res) {
    await proxyRequestToQMAPI(req, res, req.body);
});
router.delete('/api/*', async function(req, res) {
    await proxyRequestToQMAPI( req, res, req.body);
});
router.put('/api/*', async function(req, res) {
    await proxyRequestToQMAPI(req, res, req.body);
});
// router.use('/api', proxy(qm.api.getQMApiOrigin(), {
//   proxyReqOptDecorator: function(proxyReqOpts, srcReq) {
//     // you can update headers
//     // proxyReqOpts.headers['X-Client-ID'] = process.env.QUANTIMODO_CLIENT_ID;
//     // proxyReqOpts.headers['X-Client-Secret'] = process.env.QUANTIMODO_CLIENT_SECRET;
//     const accessToken = authHelper.getAccessTokenFromRequest(srcReq);
//     if(accessToken){
//         logRequest(srcReq, "Using access token from request: " + accessToken+ " at " +srcReq.path);
//       proxyReqOpts.headers['authorization'] = `Bearer ${accessToken}`;
//     } else {
//         logRequest(srcReq, "No access token in request"+ srcReq.path);
//     }
//     proxyReqOpts.rejectUnauthorized = process.env['PROXY_REJECT_UNAUTHORIZED'] || false
//     proxyReqOpts.headers['X-Client-ID'] = qm.getClientId();
//     proxyReqOpts.headers['Accept'] = 'application/json';
//     return proxyReqOpts;
//   },
//   proxyReqPathResolver: function (req) {
//     req.url = '/api' + req.url;
//     console.log('proxyReqPathResolver: '+ req.url)
//     return req.url;
//   },
//   userResDecorator: function(proxyRes, proxyResData, userReq, userRes) {
//     try {
//       let str = proxyResData.toString();
//       let data = JSON.parse(str);
//       qmLog.debug('userResDecorator', {
//         proxyRes: proxyRes,
//         proxyResData: proxyResData,
//         userReq: userReq,
//         userRes: userRes,
//       });
//       return JSON.stringify(data);
//     } catch (e) {
//       qmLog.error(e.message, {
//         e: e,
//         proxyRes: proxyRes,
//         proxyResData: proxyResData,
//         userReq: userReq,
//         userRes: userRes,
//       });
//       return proxyResData;
//     }
//   },
//   proxyErrorHandler: function (err, res, next) {
//     if(err && err.code){
//       qmLog.error('proxyErrorHandler', {
//         err: err,
//         res: res
//       });
//     }
//     switch (err && err.code) {
//       //case 'ECONNRESET':    { return res.status(405).send('504 became 405'); }
//       //case 'ECONNREFUSED':  { return res.status(200).send('gotcha back'); }
//       default:              { next(err); }
//     }
//     next(err);
//   }
// }));

module.exports = router;
