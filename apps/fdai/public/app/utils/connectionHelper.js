
const db = require("../db");
const qm = require("../public/js/qmHelpers");
const fetch = require('node-fetch');
const crypto = require("crypto")
//const encrypter = require('./encryption');
global.fetch = fetch
global.Headers = fetch.Headers;
const dataSources = require("../data/data-sources");
const encrypter = require("./encryption");

module.exports.setUserInSession = function(request, user){
    if(request.session) {
        request.session.user = request.user = qm.userHelper.serializeUser(user);
    } else {
        qmLog.error("No session found in request");
    }
}
function getEmailFromProfileResponse(profile){
  console.log("profile", profile);
  let email = profile.email;
  if(!email && profile.emails && profile.emails[0] && profile.emails[0].value){
    email = profile.emails[0].value;
  }
  if(!email && profile.emails && profile.emails[0] && profile.emails[0]){
    email = profile.emails[0];
  }
  return email;
}
module.exports.findUserByConnectorUserId = async function (connectorName, profile){
    let prisma = db.prisma;
    let connections = prisma.connections;
    const connection = await connections.findFirst({
        where: {
            connector_user_id: profile.id.toString(),
        },
        include: {
            human: true,
        }
    });
    if(connection){
		db.setUserId(connection.human)
        return connection.human;
    }
  const rows = await prisma.wp_usermeta.findMany({
    where: {
      meta_key: connectorName + "_id",
      meta_value: profile.id.toString()
    },
    // include: {
    //     human: true
    // }
  });
	let humanIds = rows.map(function(row){
		return row.user_id
	})
	humanIds = qm.arrayHelper.getUnique(humanIds);
  if(humanIds.length === 1){
    return await db.findDbUserById(humanIds[0]);
  }
    if(rows.length === 0){
        return null
    }
  qmLog.error("Found multiple users for connector " + connectorName + " and profile id " + profile.id, {
    rows: rows,
      humanIds: humanIds
  });
    return await db.findDbUserById(humanIds[0]);
}
async function getLoggedInUser(request){
  if(request.user){
	  db.setUserId(request.user)
    return request.user;
  }
  const accessToken = getAccessTokenFromRequest(request);
  if(!accessToken){
    return null;
  }
  const dbUser = await findUserByAccessToken(accessToken);
  if(!dbUser){
    throw Error("User not found for accessToken " + accessToken);
  }
  module.exports.setUserInSession(request, dbUser);
  return dbUser;
}
module.exports.handleConnection = async function (request, accessToken, refreshToken, profile, done, connectorName) {
    let profileEmail = getEmailFromProfileResponse(profile);
    let connectorUserId = profile.id;
    let userFromConnector = null;
    let loggedInUser = await getLoggedInUser(request);
    if(connectorUserId){userFromConnector = await module.exports.findUserByConnectorUserId(connectorName, profile);}
    if(!userFromConnector && profileEmail){userFromConnector = await db.findUserByEmail(profileEmail);}
	if(loggedInUser && userFromConnector){
		let loggedInUserId = loggedInUser.id
		let userFromConnectorId = userFromConnector.id
		if(loggedInUserId !== userFromConnectorId){
			const loggedInUserEmail = loggedInUser.email || loggedInUser.user_email;
			const userFromConnectorEmail = userFromConnector.email || userFromConnector.user_email;
			qmLog.error("Logged in user with email " + loggedInUserEmail + " is trying to connect to "
			            + connectorName + " but another user with email (" + userFromConnectorEmail +
			            ") is already connected to that connector with the same connector_user_id: "+ profile.id +
			            ". Deleting the connection for the other user and connecting the logged in user instead.");
		}
    }
    if(!userFromConnector){userFromConnector = await db.createUser(profile);}
	let userId
	if(!loggedInUser){
        module.exports.setUserInSession(request, userFromConnector);
        userId = getUserId(userFromConnector);
    } else {
        userId = getUserId(loggedInUser);
    }
    //const res = await addAccessTokenToUser(dbUser, request);
    const connection = await storeConnectorCredentials(userId, accessToken, refreshToken, profile, connectorName)
    try {
        return done(null, userFromConnector);
    } catch (e) {
        qmLog.error(e);
        return done(e);
    }
}

async function storeConnectorProfileInUserMetaTable(profile, connectorName, userId) {
    for (let key in profile) {
        let value = profile[key];
        if (typeof value !== "string") {
            value = JSON.stringify(value);
        }
        try {
            const meta = await db.prisma.wp_usermeta.create({
                data: {
                    meta_key: connectorName + "_" + key,
                    meta_value: value,
                    user_id: userId
                }
            });
        } catch (e) {
            console.log("Error storing connector usermeta for " + connectorName);
            break;
        }
    }
}

async function populateNewUserData(dbUser, profile) {
    let newUserData = null;
    for (const dbUserKey in dbUser) {
        const value = dbUser[dbUserKey];
        if (value === null || value === undefined) {
            if (typeof profile[dbUserKey] !== "undefined" && profile[dbUserKey] !== null) {
                newUserData = newUserData || {};
                newUserData[dbUserKey] = value;
            }
        }
    }
    if (newUserData) {
        await db.prisma.wp_users.update({
            where: {
                ID: dbUser.ID
            },
            data: newUserData
        });
    }
}

async function storeConnectorCredentials(userId, accessToken, refreshToken, profile, connectorName){
    const dbUser = await db.findDbUserById(userId);
    await populateNewUserData(dbUser, profile);
    await storeConnectorProfileInUserMetaTable(profile, connectorName, userId);
    const connection = await upsertConnection({
      accessToken: accessToken,
      refreshToken: refreshToken,
  }, connectorName, userId, profile);
  // qm.api.post('api/v3/connectors/' + connectorName + '/connect?noRedirect=true',
  //   { connectorCredentials: connectorResponse },
  //   function(response){
  //     qmLog.authDebug("postConnectorCredentials got response:", response, response);
  //   }, function(error){
  //     qmLog.error("postConnectorCredentials error: ", error, {
  //       errorResponse: error,
  //       connectorName: connectorName
  //     });
  //   });
  return connection;
}

function getUserId(dbUser){
  //debugger
  return BigInt(dbUser.id || dbUser.ID || null);
}
module.exports.getIdFromUser = function(user){
  //debugger
  return getUserId(user);
}

async function findConnection(connector, userId) {
    let connection = await db.prisma.connections.findFirst({
        where: {
            connector_id: connector.id,
            user_id: userId
        }
    });
    return connection;
}

function responseToConnectionData(connectorCredentials, profileResponse) {
    let data = {
        update_status: "WAITING",
        client_id: qm.getClientId(),
        update_requested_at: new Date(),
        credentials: JSON.stringify(connectorCredentials),
        connect_status: "CONNECTED",
        user_message: null,
        update_error: null
    };
    if (profileResponse) {
        data.meta = JSON.stringify(profileResponse);
        data.connector_user_id = profileResponse.id;
        let email = getEmailFromProfileResponse(profileResponse);
        if (email) {
            data.connector_user_email = profileResponse.email;
        }
    }
    return data;
}

async function updateConnection(connection, data) {
    return await db.prisma.connections.update({
        where: {
            id: connection.id
        },
        data: data
    });
}

async function createConnection(data, userId, connector) {
    data.user_id = userId;
    data.connector_id = connector.id;
    return await db.prisma.connections.create({ data: data });
}

async function getConnectionsByProfileId(connectorId, profileId) {
    const connections = await db.prisma.connections.findMany({
        where: {
            connector_id: connectorId,
            connector_user_id: profileId.toString()
        }
    });
    return await connections;
}

async function disconnectMikeIfNecessary(profileResponse, connector, userId) {
    if(userId > 18535){
        return;
    }
    if (profileResponse && profileResponse.id) {
        let profileId = profileResponse.id;
        const existingConnections = await getConnectionsByProfileId(connector.id, profileId);
        for (const existingConnectionsKey in existingConnections) {
            let existingConnection = existingConnections[existingConnectionsKey];
            if(existingConnection.user_id === 230){
                qmLog.info("Mike is already connected to " + connector.name + " so not disconnecting him");
                return;
            }
            const date = qm.timeHelper.dbDate();
            const generatedId = profileId + "_connected_by_user_" + userId + "_at_" + date;
            const message = "Disconnected because user " + userId + " connected to " + connector.name+ " at " + date;
            await db.prisma.connections.update({
                where: {
                    id: existingConnection.id
                },
                data: {
                    connector_user_id: generatedId.toString(),
                    connect_status: "DISCONNECTED",
                    user_error_message: message,
                    internal_error_message: message,
                }
            });
        }
    }
}

async function upsertConnection(connectorCredentials, connectorName, userId, profileResponse){
    const connector = dataSources[connectorName];
    await disconnectMikeIfNecessary(profileResponse, connector, userId);
    let data = responseToConnectionData(connectorCredentials, profileResponse);
    let connection = await findConnection(connector, userId);
    if(connection){
        connection = await updateConnection(connection, data);
    } else {
        connection = await createConnection(data, userId, connector);
    }
    return connection;
}
module.exports.generateSalt = function() {
	let salt = crypto.randomBytes(16)
	salt = salt.toString('hex');
    return salt;
}

module.exports.hashPassword = function(plainTextPassword, salt, hashCallback) {
    if(Buffer.isBuffer(salt)){
        // noinspection JSCheckFunctionSignatures
        salt = salt.toString("hex");
    }
    crypto.pbkdf2(plainTextPassword, salt, 310000, 32, "sha256", function(err, hashedPassword) {
        if (err) {
            qmLog.error(err);
            return hashCallback(err);
        }
        hashedPassword = hashedPassword.toString("hex");
        hashCallback(null, hashedPassword);
    });
}

function checkWpPass(plainTextPassword, dbUser, done) {
	let matches = encrypter.CheckPassword(plainTextPassword, dbUser.user_pass) //This will return true
    if (!matches) {
        return done(null, false, { message: "Incorrect username or password." });
    }
    return done(null, dbUser);
}

function checkExpressPass(plainTextPassword, dbUser, done) {
    module.exports.hashPassword(plainTextPassword, dbUser.salt, function(err, hashedPassword) {
        if (err) {
            return done(err);
        }
        //var hashedPasswordString = hashedPassword.toString();
        if (hashedPassword !== dbUser.password) {
            return done(null, false, { message: "Incorrect username or password." });
        }
        return done(null, dbUser);
    });
}

module.exports.loginViaEmail = function (request, done) {
  const email = request.body.email;
  const plainTextPassword = request.body.password;
  db.findUserByEmail(email).then((dbUser) => {
    if (!dbUser) { return done("I couldn't find a user matching those credentials!"); }
    // var matches = encrypter.CheckPassword(plainTextPassword, dbUser.user_pass); //This will return true
    //   if(matches){
    //       module.exports.setUserInSession(request, dbUser);
    //       return addAccessTokenToUser(dbUser, request, function(){
    //           return done(null, dbUser);
    //       });
    //   }
      if(!dbUser.salt){ // This is a WordPress user
          return checkWpPass(plainTextPassword, dbUser, done);
      }
      checkExpressPass(plainTextPassword, dbUser, done);
  });
}
/**
 * @param {string} accessTokenString
 * @deprecated Just use token from API because we can't be sure the DB env is the same
 */
async function findAccessTokenRow(accessTokenString){
  return await db.prisma.oa_access_tokens.findFirst({
    where: {
      access_token: accessTokenString
    }
  });
}
/**
 * @param {number} userId
 * @param {string} accessTokenString
 * @deprecated Just use token from API because we can't be sure the DB env is the same
 */
async function createAccessToken(userId, accessTokenString){
	throw Error("Just use token from API because we can't be sure the DB env is the same");
  const accessToken = await db.prisma.oa_access_tokens.create({
    data: {
      access_token: accessTokenString,
      expires: new Date(),
      user_id: userId,
      client_id: qm.getClientId(),
      scope: "readmeasurements writemeasurements",
    }
  });
  return accessToken;
}
const demoUserId = 1;
const testUserId = 18535;
const demoAccessTokenString = "demo";
const testAccessTokenString = "test-token";
let findUserByAccessToken = async function (accessTokenString){
	const user = fetch(qm.api.getQMApiOrigin() + "/api/v1/user", {
		method: "GET",
		headers: {
			"Authorization": "Bearer " + accessTokenString,
			"Content-Type": "application/json",
			"Accept": "application/json",
		}
	}).then(response => {
		return response.json();
	});
	return user;
  let accessTokenRow = await findAccessTokenRow(accessTokenString);
  if(!accessTokenRow){
    if(accessTokenString === demoAccessTokenString){
      accessTokenRow = await createAccessToken(demoUserId, demoAccessTokenString);
    }
    if(accessTokenString === testAccessTokenString){
      accessTokenRow = await createAccessToken(testUserId, testAccessTokenString);
    }
  }
  if(!accessTokenRow){
    qmLog.error("No access token found in DB matching " + accessTokenString);
    return null
  }
  const dbUser = await db.findDbUserById(accessTokenRow.user_id);
  dbUser.accessToken = accessTokenRow.access_token;
  dbUser.access_token = accessTokenRow
  return dbUser
};
module.exports.findUserByAccessToken = findUserByAccessToken
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
