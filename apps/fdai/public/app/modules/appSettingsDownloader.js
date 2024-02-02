const {writeAppSettingsToFile} = require("../ts/qm.app-settings");
const sdk = require('api')('@curedao/v1.0#lmp2v23l1ecjhnr');
sdk.getAppSettings({clientId: 'oauth_test_client'})
    .then(function (r) {
        //fs.writeFileSync('../public/appSettings.json', JSON.stringify(r.appSettings, null, 4));
        //fs.writeFileSync('../public/appSettings.json', JSON.stringify(r.appSettings, null, 4));
        return writeAppSettingsToFile(r.appSettings);
    })
    .catch(err => console.error(err));
