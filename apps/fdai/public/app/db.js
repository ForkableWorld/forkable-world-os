var crypto = require('crypto');
const { PrismaClient } = require('@prisma/client')
var randomBytes = require('bluebird').promisify(crypto.randomBytes);
const qm = require("./public/js/qmHelpers");
const prisma = new PrismaClient()
BigInt.prototype["toJSON"] = function () {
  return this.toString();
};

async function main() {
  // ... you will write your Prisma Client queries here
}

main()
  .then(async () => {
    await prisma.$disconnect()
  })
  .catch(async (e) => {
    console.error(e)
    await prisma.$disconnect()
    process.exit(1)
  })


async function generateRandomToken() {
  const t = await randomBytes(256).then(function(buffer) {
    return crypto
      .createHash('sha1')
      .update(buffer)
      .digest('hex');
  });
  return t;
}

async function createAccessToken(user){
  let unixTimestamp = Math.round(+new Date()/1000);
  unixTimestamp += (30*86400);
  var date = qm.timeHelper.iso(unixTimestamp)
  let accessToken = await generateRandomToken();
  let tokenData = {
    data: {
      user_id: user.ID,
      expires: date,
      access_token: accessToken,
      client_id: qm.getClientId(),
      scope: "readmeasurements writemeasurements",
    },
  };
  //if(qm.appMode.isDebug()){debugger;}
  const token = await prisma.oa_access_tokens.create(tokenData)
  user.access_token = token;
  return token
}
async function findDbUserById(ID){
    if(qm.appMode.isDebug()){debugger;}
  const user = await prisma.wp_users.findUnique({
    where: {
      ID: ID,
    },
  })
	setUserId(user)
  return user
}
async function findUserByEmail(email){
    //if(qm.appMode.isDebug()){debugger;}
  let user = await prisma.wp_users.findUnique({
    where: {
      email: email,
    }
  })
	if(!user){ // TODO: remove this after all users have been migrated to new db
		user = await prisma.wp_users.findUnique({
			where: {
				user_email: email,
			}
		})
	}
  if(!user){
    qmLog.warn("User not found for email " + email);
    return null
  }
  return setUserId(user)
}

function setUserId(user){
	if(!user){return null}
	if(!user.id){user.id = user.ID}
	user.id = parseInt(user.id)
	return user
}

async function createUser(data) {
  let email = data.email;
  let password = data.password || data.token || await generateRandomToken();
    //if(qm.appMode.isDebug()){debugger;}
    if(!data.user_login){data.user_login = email}
    if(!data.user_email){data.user_email = email}
	if(!data.email){data.email = email}
    if(!data.user_pass){data.user_pass = password}
    data.client_id = qm.getClientId()
    const user = await prisma.wp_users.create({ data: data, })
    setUserId(user)
  console.log(`Created new user: ${user.email} (ID: ${user.id})`)
  return user
}

module.exports =  {
  prisma,
  createAccessToken,
  findUserByEmail,
  findDbUserById,
  createUser,
	setUserId,
}
