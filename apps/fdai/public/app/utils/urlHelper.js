const envHelper = require("../ts/env-helper");
//envHelper.loadEnvFromDopplerOrDotEnv(null);
const port = parseInt(envHelper.getenv("EXPRESS_PORT", "5000"));
let fallbackServerUrl = `http://localhost:${port}`;

module.exports = {
  serverPort: port,
  serverOrigin: process.env[envHelper.envNames.EXPRESS_ORIGIN] || fallbackServerUrl,
  loginSuccessRedirect: process.env[envHelper.envNames.LOGIN_SUCCESS_REDIRECT] || "/#/app/onboarding",
  loginFailureRedirect: "/#/app/login",
  loginPath: "/#/app/login",
    getUrlFromReq: function(req) {
      if(!req){
          qmLog.error("getUrlFromReq: req is null");
      }
        return qm.urlHelper.getCurrentUrl(req);
    }
}
