module.exports = {
  "general_spreadsheet": {
    "id": 75,
    "timestamps": true,
    "backgroundColor": "#23448b",
    "dataSourceType": "spreadsheet_upload",
    "displayName": "General Spreadsheet",
    "enabled": 1,
    "image": "https://applets.imgix.net/https%3A%2F%2Fassets.ifttt.com%2Fimages%2Fchannels%2F799977804%2Ficons%2Fon_color_large.png%3Fversion%3D0?ixlib=rails-2.1.3&w=240&h=240&auto=compress&s=dd7fc1b8b5df19e872f5fefe14c107cd",
    "imageHtml": "\"General",
    "linkedDisplayNameHtml": "General Spreadsheet",
    "logoColor": "#2d2d2d",
    "longDescription": "Import from a spreadsheet containing a Variable Name, Value, Measurement Event Time, and Abbreviated Unit Name field. Here is an example spreadsheet with allowed column names, units and time format.",
    "name": "general_spreadsheet",
    "oauth": false,
    "shortDescription": "Import from a spreadsheet containing a Variable Name, Value, Measurement Event Time, and Abbreviated Unit Name field",
    "message": "Import from a spreadsheet containing a Variable Name, Value, Measurement Event Time, and Abbreviated Unit Name field. Here is an example spreadsheet with allowed column names, units and time format.",
    "qmClient": false,
    "spreadsheetUploadLink": "https://local.quantimo.do/api/v2/spreadsheetUpload",
    "updateRequestedAt": false,
    "updateStatus": "NEVER_UPLOADED",
    "platforms": [
      "chrome",
      "web"
    ],
    "spreadsheetUpload": true
  },
  "medhelper": {
    "id": 69,
    "timestamps": true,
    "backgroundColor": "#00afd8",
    "dataSourceType": "spreadsheet_upload",
    "defaultUnitAbbreviatedName": "Milligrams",
    "defaultVariableCategoryName": "Treatments",
    "displayName": "MedHelper",
    "enabled": 1,
    "image": "https://static.quantimo.do/img//connectors/medhelper.png",
    "imageHtml": "\"MedHelper\"",
    "linkedDisplayNameHtml": "MedHelper",
    "logoColor": "#2d2d2d",
    "longDescription": "MedHelper is a comprehensive prescription/medication compliance and tracking App designed to help individuals and caretakers manage the challenges of staying on time up to date and on schedule with very simple to very complex regimes. Easy to install and full featured MedHelper is ready to become your 24/7 healthcare assistant. Available on Android and IOS platforms.",
    "name": "medhelper",
    "oauth": false,
    "shortDescription": "Tracks medications.",
    "message": "MedHelper is a comprehensive prescription/medication compliance and tracking App designed to help individuals and caretakers manage the challenges of staying on time up to date and on schedule with very simple to very complex regimes. Easy to install and full featured MedHelper is ready to become your 24/7 healthcare assistant. Available on Android and IOS platforms.",
    "qmClient": false,
    "spreadsheetUploadLink": "https://local.quantimo.do/api/v2/spreadsheetUpload",
    "updateRequestedAt": false,
    "updateStatus": "NEVER_UPLOADED",
    "platforms": [
      "chrome",
      "web"
    ],
    "spreadsheetUpload": true
  },
  "mint-spreadsheet": {
    "id": 73,
    "timestamps": true,
    "backgroundColor": "#4cd964",
    "dataSourceType": "spreadsheet_upload",
    "defaultUnitAbbreviatedName": "$",
    "defaultVariableCategoryName": "Payments",
    "displayName": "Mint Spreadsheet Upload",
    "enabled": 1,
    "image": "https://static-s.aa-cdn.net/img/gp/20600000039023/Vd9ubTf3GHgogznhGqYLb-hFtx6gqhZ5h5wBzSf-1Wf_GsFHRf1Lk_HX0muiCTp1fL_u=w300?v=1",
    "imageHtml": "\"Mint",
    "linkedDisplayNameHtml": "Mint Spreadsheet Upload",
    "logoColor": "#2d2d2d",
    "longDescription": "Upload an exported transactions spreadsheet from Mint.com. Manage your money, pay your bills and track your credit score with Mint. Now that's being good with your money. ",
    "name": "mint-spreadsheet",
    "oauth": false,
    "shortDescription": "Tracks expenditures",
    "message": "Upload an exported transactions spreadsheet from Mint.com. Manage your money, pay your bills and track your credit score with Mint. Now that's being good with your money. ",
    "qmClient": false,
    "spreadsheetUploadLink": "https://local.quantimo.do/api/v2/spreadsheetUpload",
    "updateRequestedAt": false,
    "updateStatus": "NEVER_UPLOADED",
    "platforms": [
      "chrome",
      "web"
    ],
    "spreadsheetUpload": true
  },
  "air-quality": {
    "id": 90,
    "timestamps": true,
    "backgroundColor": "#1e2023",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Environment",
    "displayName": "Air Quality",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "image": "https://static.quantimo.do/img/connectors/air-quality-connector.jpg",
    "logoColor": "#2d2d2d",
    "longDescription": "Automatically import particulate pollution, ozone pollution and air quality.",
    "name": "air-quality",
    "oauth": false,
    "shortDescription": "Tracks pollution",
    "synonyms": [
      "Air Quality Index"
    ],
    "userId": 7,
    "maximumRequestTimeSpanInSeconds": 86400,
    "availableOutsideUS": false,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/air-quality/connect",
      "parameters": [
        {
          "displayName": "Postal Code",
          "key": "zip",
          "type": "text",
          "placeholder": "Enter your zip code"
        }
      ],
      "usePopup": false,
      "text": "Enter your postal code"
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "variableNames": [
      "Fine Particulate Matter Pollution Air Quality Index",
      "Large Particulate Matter Pollution Air Quality Index",
      "Ozone Pollution Air Quality Index"
    ],
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ]
  },
  "daylight": {
    "id": 92,
    "timestamps": true,
    "backgroundColor": "#1e2023",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Environment",
    "displayName": "Daylight",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "image": "https://static.quantimo.do/img/agriculture/png/sun.png",
    "logoColor": "#2d2d2d",
    "longDescription": "Automatically import the number of hours between sunrise and sunset to see if you might be affected by Seasonal Affective Disorder.",
    "name": "daylight",
    "oauth": false,
    "shortDescription": "Tracks hours of daylight",
    "synonyms": [
      "Hours of Daylight"
    ],
    "userId": 7,
    "maximumRequestTimeSpanInSeconds": 86400,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/daylight/connect",
      "parameters": [
        {
          "displayName": "Postal Code",
          "key": "zip",
          "type": "text",
          "placeholder": "Enter your zip code"
        }
      ],
      "usePopup": false,
      "text": "Enter your postal code"
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "variableNames": [
      "Time Between Sunrise And Sunset"
    ],
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ]
  },
  "facebook": {
    "id": 8,
    "timestamps": true,
    "backgroundColor": "#3b579d",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Social Interactions",
    "displayName": "Facebook",
    "enabled": 1,
    "fontAwesome": "fab fa-facebook",
    "getItUrl": "https://www.facebook.com",
    "image": "https://applets.imgix.net/https%3A%2F%2Fassets.ifttt.com%2Fimages%2Fchannels%2F29%2Ficons%2Fon_color_large.png%3Fversion%3D0?ixlib=rails-2.1.3&w=240&h=240&auto=compress&s=216b3768523a87b6eadc8f3ee46dcef7",
    "logoColor": "#3b5998",
    "longDescription": "Facebook is a social networking website where users may create a personal profile, add other users as friends, and exchange messages.",
    "name": "facebook",
    "oauth": true,
    "shortDescription": "Tracks social interaction. QuantiModo requires permission to access your Facebook \"user likes\" and \"user posts\".",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://www.facebook.com/dialog/oauth?state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=225078261031461&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Ffacebook%2Fconnect&response_type=code",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "providesUserProfileForLogin": true,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "225078261031461"
  },
  "fitbit": {
    "id": 7,
    "timestamps": true,
    "backgroundColor": "#cc73e1",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Physical Activity",
    "displayName": "Fitbit",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://www.amazon.com/Fitbit-Charge-Heart-Fitness-Wristband/dp/B01K9S260E/ref=as_li_ss_tl?ie=UTF8&qid=1493518902&sr=8-3&keywords=fitbit&th=1&linkCode=ll1&tag=quantimodo04-20&linkId=b357b0833de73b0c4e935fd7c13a079e",
    "image": "https://static.quantimo.do/img/connectors/fitbit.png",
    "logoColor": "#4cc2c4",
    "longDescription": "Fitbit makes activity tracking easy and automatic.",
    "name": "fitbit",
    "oauth": true,
    "shortDescription": "Tracks sleep, diet, and physical activity.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://www.fitbit.com/oauth2/authorize?state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=2298DS&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Ffitbit%2Fconnect&response_type=code&scope=activity+heartrate+location+nutrition+sleep+weight+profile+settings+social",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "2298DS",
    "scopes": [
      "activity",
      "heartrate",
      "location",
      "nutrition",
      "sleep",
      "weight",
      "profile",
      "settings",
      "social"
    ]
  },
  "github": {
    "id": 4,
    "timestamps": true,
    "backgroundColor": "#e4405f",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Activities",
    "displayName": "GitHub",
    "enabled": 1,
    "fontAwesome": "fab fa-github",
    "getItUrl": "https://github.com/",
    "image": "https://i.imgur.com/eUiQNlk.png",
    "logoColor": "#2d2d2d",
    "longDescription": "GitHub is the best place to share code with friends, co-workers, classmates, and complete strangers. Over four million people use GitHub to build amazing things together.",
    "name": "github",
    "oauth": true,
    "shortDescription": "Tracks code commits.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://github.com/login/oauth/authorize?state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=b7bf465391ed9601ba49&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Fgithub%2Fconnect&response_type=code&scope=user%2Crepo",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "providesUserProfileForLogin": true,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "b7bf465391ed9601ba49",
    "scopes": [
      "user",
      "repo"
    ]
  },
  "googlefit": {
    "id": 61,
    "timestamps": true,
    "backgroundColor": "#00a3ad",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Physical Activity",
    "displayName": "Google Fit",
    "enabled": 1,
    "fontAwesome": "fab fa-google",
    "getItUrl": "https://fit.google.com/",
    "image": "https://i.imgur.com/QGtGtGT.png",
    "logoColor": "#d34836",
    "longDescription": "Use Google Fit to import your fitness data.",
    "name": "googlefit",
    "oauth": true,
    "shortDescription": "Tracks Calories Burned, Heart Rate, Body Weight, Hourly Step Count, and Walk or Run Distance",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://accounts.google.com/o/oauth2/auth?access_type=offline&approval_prompt=force&prompt=consent&state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=1052648855194.apps.googleusercontent.com&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Fgooglefit%2Fconnect&response_type=code&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Ffitness.activity.read+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Ffitness.body.read+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Ffitness.location.read+openid+profile+email",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "providesUserProfileForLogin": false,
    "variableNames": [
      "Calories Burned",
      "Heart Rate (Pulse)",
      "Walk Or Run Distance"
    ],
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "1052648855194.apps.googleusercontent.com",
    "scopes": [
      "https://www.googleapis.com/auth/fitness.activity.read",
      "https://www.googleapis.com/auth/fitness.body.read",
      "https://www.googleapis.com/auth/fitness.location.read",
      "openid",
      "profile",
      "email"
    ],
    "oauthServiceName": "Google"
  },
  "googleplus": {
    "id": 84,
    "createdAt": "2022-11-23 07:11:40",
    "updatedAt": "2022-11-24 05:05:57",
    "timestamps": true,
    "crappy": true,
    "backgroundColor": "#23448b",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Social Interactions",
    "displayName": "Google",
    "enabled": 1,
    "fontAwesome": "fab fa-google",
    "image": "https://static.quantimo.do/img/connectors/google-logo-icon-PNG-Transparent-Background.png",
    "logoColor": "#d34836",
    "longDescription": "Imports your profile information from Google",
    "name": "googleplus",
    "numberOfMeasurements": 0,
    "oauth": true,
    "shortDescription": "Imports profile information",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://accounts.google.com/o/oauth2/auth?access_type=offline&approval_prompt=force&prompt=consent&state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=1052648855194.apps.googleusercontent.com&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Fgoogleplus%2Fconnect&response_type=code&scope=openid+profile+email",
      "usePopup": true
    },
    "connectStatus": "CONNECTED",
    "importViaApi": false,
    "message": "Imports your profile information from Google",
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "providesUserProfileForLogin": true,
    "updateRequestedAt": "2022-11-23 07:11:40",
    "updateStatus": "WAITING",
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "1052648855194.apps.googleusercontent.com",
    "scopes": [
      "openid",
      "profile",
      "email"
    ],
    "oauthServiceName": "Google"
  },
  "linkedin": {
    "id": 82,
    "timestamps": true,
    "backgroundColor": "#351313",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Social Interactions",
    "displayName": "LinkedIn",
    "enabled": 1,
    "fontAwesome": "fab fa-linkedin",
    "getItUrl": "https://linkedin.com/",
    "image": "https://static.quantimo.do/img/connectors/linkedin-connector.png",
    "logoColor": "#0e76a8",
    "longDescription": "Manage your professional identity. Build and engage with your professional network. Access knowledge, insights and opportunities.",
    "name": "linkedin",
    "oauth": true,
    "shortDescription": "Tracks social interaction.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://www.linkedin.com/oauth/v2/authorization?state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=77oiwiq6wuq9uy&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Flinkedin%2Fconnect&response_type=code&scope=r_liteprofile+r_emailaddress",
      "usePopup": true
    },
    "importViaApi": false,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "providesUserProfileForLogin": true,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "77oiwiq6wuq9uy",
    "scopes": [
      "r_liteprofile",
      "r_emailaddress"
    ]
  },
  "moodscope": {
    "id": 5,
    "timestamps": true,
    "crappy": true,
    "backgroundColor": "#FFFFFF",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Emotions",
    "displayName": "Moodscope",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://www.moodscope.com",
    "image": "https://i.imgur.com/ymn6gRq.png",
    "logoColor": "#ff0000",
    "longDescription": "MoodScope is a web based application for measuring, tracking and sharing your mood. Moods are measured using an online card game, and can be shared automatically by email with friends, with the idea that these activities can raise mood in and of themselves. The mood log can be charted to see progressions and as a way to identify events that may have influenced your mood.",
    "name": "moodscope",
    "oauth": false,
    "shortDescription": "Tracks mood.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/moodscope/connect",
      "parameters": [
        {
          "displayName": "Username",
          "key": "username",
          "type": "text"
        },
        {
          "displayName": "Password",
          "key": "password",
          "type": "password"
        }
      ],
      "usePopup": false,
      "text": "Enter your credentials"
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ]
  },
  "myfitnesspal": {
    "id": 1,
    "timestamps": true,
    "backgroundColor": "#262626",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Foods",
    "displayName": "MyFitnessPal",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://www.amazon.com/gp/product/B004H6WTJI/ref=as_li_qf_sp_asin_il?ie=UTF8&camp=1789&creative=9325&creativeASIN=B004H6WTJI&linkCode=as2&tag=quantimodo04-20",
    "image": "https://i.imgur.com/2aUrwtd.png",
    "logoColor": "#2d2d2d",
    "longDescription": "Lose weight with MyFitnessPal, the fastest and easiest-to-use calorie counter for iPhone and iPad. With the largest food database of any iOS calorie counter (over 3,000,000 foods), and amazingly fast food and exercise entry.",
    "name": "myfitnesspal",
    "oauth": false,
    "shortDescription": "Tracks diet.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/myfitnesspal/connect",
      "parameters": [
        {
          "displayName": "Username",
          "key": "username",
          "type": "text"
        },
        {
          "displayName": "Password",
          "key": "password",
          "type": "password"
        }
      ],
      "usePopup": false,
      "text": "Enter your credentials"
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ]
  },
  "mynetdiary": {
    "id": 12,
    "timestamps": true,
    "backgroundColor": "#4cd964",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Foods",
    "displayName": "MyNetDiary",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://www.amazon.com/gp/product/B00BFEVFP4/ref=as_li_qf_sp_asin_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00BFEVFP4&linkCode=as2&tag=quantimodo04-20",
    "image": "https://i.imgur.com/yqm06Zg.png",
    "logoColor": "#2d2d2d",
    "longDescription": "MyNetDiary is an online and mobile food diary with calorie counter and online community. MyNetDiary provides instant and easy food entry, searching while you type. Enter foods 2-3 times faster than with any other food diary.",
    "name": "mynetdiary",
    "oauth": false,
    "shortDescription": "Tracks diet and exercise.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/mynetdiary/connect",
      "parameters": [
        {
          "displayName": "Username",
          "key": "username",
          "type": "text"
        },
        {
          "displayName": "Password",
          "key": "password",
          "type": "password"
        }
      ],
      "usePopup": false,
      "text": "Enter your credentials"
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ]
  },
  "netatmo": {
    "id": 74,
    "timestamps": true,
    "backgroundColor": "#388bbe",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Environment",
    "displayName": "Netatmo",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://amzn.to/2uCqcIH",
    "image": "https://is4-ssl.mzstatic.com/image/thumb/Purple118/v4/c0/e1/e3/c0e1e38b-4eda-ea50-cb83-1e1d43b9e9bb/AppIcon-1x_U007emarketing-85-220-0-6.png/246x0w.jpg",
    "logoColor": "#2d2d2d",
    "longDescription": "Experience the comfort of a Smart Home: Smart Thermostat, Security Camera with Face Recognition, Weather Station.",
    "name": "netatmo",
    "oauth": true,
    "shortDescription": "Tracks humidity and temperature",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://api.netatmo.com/oauth2/authorize?state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=58115c8b7a75702f8f8b72c1&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Fnetatmo%2Fconnect&response_type=code&scope=read_station+read_thermostat",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "variableNames": [
      "Indoor Humidity",
      "Indoor Noise",
      "Indoor CO2",
      "Indoor Pressure",
      "Indoor Temperature"
    ],
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "58115c8b7a75702f8f8b72c1",
    "scopes": [
      "read_station",
      "read_thermostat"
    ]
  },
  "oura": {
    "id": 98,
    "timestamps": true,
    "backgroundColor": "#cc73e1",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Physical Activity",
    "displayName": "Oura",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://ouraring.com",
    "image": "https://static.quantimo.do/img/connectors/oura-connector.png",
    "logoColor": "#4cc2c4",
    "longDescription": "Oura makes activity tracking easy and automatic.",
    "name": "oura",
    "oauth": true,
    "shortDescription": "Tracks sleep, diet, and physical activity.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://cloud.ouraring.com/oauth/authorize?state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=VRGK4JRCUKHSMK2R&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Foura%2Fconnect&response_type=code&scope=heartrate+session+tag+workout+daily+personal+email+session",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "providesUserProfileForLogin": true,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "VRGK4JRCUKHSMK2R",
    "scopes": [
      "heartrate",
      "session",
      "tag",
      "workout",
      "daily",
      "personal",
      "email",
      "session"
    ]
  },
  "pollen-count": {
    "id": 91,
    "timestamps": true,
    "backgroundColor": "#1e2023",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Environment",
    "displayName": "Pollen Count",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "image": "https://s3.amazonaws.com/static.quantimo.do/img/pollen.png",
    "logoColor": "#2d2d2d",
    "longDescription": "Automatically import pollen count for various species and find out how it could be affecting your symptoms.",
    "name": "pollen-count",
    "oauth": false,
    "shortDescription": "Tracks pollen count",
    "synonyms": [
      "Pollen"
    ],
    "userId": 7,
    "availableOutsideUS": false,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/pollen-count/connect",
      "parameters": [
        {
          "displayName": "Postal Code",
          "key": "zip",
          "type": "text",
          "placeholder": "Enter your zip code"
        }
      ],
      "usePopup": false,
      "text": "Enter your postal code"
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "variableNames": [
      "Pollen Index"
    ],
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ]
  },
  "quantimodo": {
    "id": 72,
    "createdAt": "2022-11-23 07:28:07",
    "updatedAt": "2022-11-23 07:28:07",
    "timestamps": true,
    "backgroundColor": "#e4405f",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Symptoms",
    "displayName": "QuantiModo",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://quantimo.do",
    "image": "https://static.quantimo.do/img/logos/quantimodo-logo-qm-rainbow-200-200.png",
    "longDescription": "QuantiModo allows you to easily track mood, symptoms, or any outcome you want to optimize in a fraction of a second. You can also import your data from over 30 other apps and devices. QuantiModo then analyzes your data to identify which hidden factors are most likely to be influencing your mood or symptoms.",
    "name": "quantimodo",
    "oauth": true,
    "shortDescription": "Tracks anything",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://app.quantimo.do/oauth/authorize?register=true&state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=quantimodo&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Fquantimodo%2Fconnect&response_type=code&scope=readmeasurements+writemeasurements",
      "usePopup": true
    },
    "connectStatus": "CONNECTED",
    "importViaApi": false,
    "message": "QuantiModo allows you to easily track mood, symptoms, or any outcome you want to optimize in a fraction of a second. You can also import your data from over 30 other apps and devices. QuantiModo then analyzes your data to identify which hidden factors are most likely to be influencing your mood or symptoms.",
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "providesUserProfileForLogin": true,
    "updateRequestedAt": "2022-11-23 07:28:07",
    "updateStatus": "WAITING",
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "quantimodo",
    "scopes": [
      "readmeasurements",
      "writemeasurements"
    ]
  },
  "rescuetime": {
    "id": 11,
    "timestamps": true,
    "backgroundColor": "#2f78bd",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Activities",
    "displayName": "RescueTime",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://www.rescuetime.com/rp/quantimodo/plans",
    "image": "https://applets.imgix.net/https%3A%2F%2Fassets.ifttt.com%2Fimages%2Fchannels%2F1829789558%2Ficons%2Fon_color_large.png%3Fversion%3D0?ixlib=rails-2.1.3&w=240&h=240&auto=compress&s=3b62550176f3456071514c8e510e8ef2",
    "logoColor": "#2d2d2d",
    "longDescription": "Detailed reports show which applications and websites you spent time on. Activities are automatically grouped into pre-defined categories with built-in productivity scores covering thousands of websites and applications. You can customize categories and productivity scores to meet your needs.",
    "name": "rescuetime",
    "oauth": true,
    "shortDescription": "Tracks productivity, phone, and computer usage.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://we-do-not-have-a-client-for-https//local.quantimo.do-yet",
      "usePopup": true
    },
    "importViaApi": true,
    "logoutUrl": "https://www.rescuetime.com/logout",
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "scopes": [
      "time_data",
      "category_data",
      "productivity_data"
    ],
    "variableCategoryName": "Software"
  },
  "runkeeper": {
    "id": 2,
    "timestamps": true,
    "backgroundColor": "#3d55a6",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Physical Activity",
    "displayName": "RunKeeper",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://www.amazon.com/gp/product/B004Z2TYTC/ref=as_li_qf_sp_asin_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B004Z2TYTC&linkCode=as2&tag=quantimodo04-20",
    "image": "https://i.imgur.com/GHhb4wb.png",
    "logoColor": "#2d2d2d",
    "longDescription": "RunKeeper is the simplest way to improve fitness, whether you're just deciding to get off the couch for a 5k, biking every day, or even deep into marathon training.\nTrack your runs, walks, bike rides, training workouts and all of the other fitness activities using the GPS in your Android Phone.",
    "name": "runkeeper",
    "oauth": true,
    "shortDescription": "Tracks your workouts.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://runkeeper.com/apps/authorize?state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=57b4df7d2adb45e38fd126dfd3e8c188&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Frunkeeper%2Fconnect&response_type=code",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "57b4df7d2adb45e38fd126dfd3e8c188"
  },
  "slack": {
    "id": 87,
    "timestamps": true,
    "backgroundColor": "#0f7965",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Social Interactions",
    "displayName": "Slack",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "image": "https://upload.wikimedia.org/wikipedia/commons/7/76/Slack_Icon.png",
    "logoColor": "#2d2d2d",
    "longDescription": "Slack brings all your communication together in one place. It's real-time messaging, archiving and search for modern teams.",
    "name": "slack",
    "oauth": true,
    "shortDescription": "Tracks social interaction",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://slack.com/oauth/authorize?state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=212721326246.211356569553&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Fslack%2Fconnect&response_type=code&scope=incoming-webhook+chat%3Awrite%3Abot+links%3Awrite",
      "usePopup": true
    },
    "importViaApi": false,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "212721326246.211356569553",
    "scopes": [
      "incoming-webhook",
      "chat:write:bot",
      "links:write"
    ]
  },
  "sleepcloud": {
    "id": 14,
    "timestamps": true,
    "backgroundColor": "#124191",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Sleep",
    "displayName": "Sleep as Android",
    "enabled": 1,
    "fontAwesome": "fab fa-google",
    "getItUrl": "https://sites.google.com/site/sleepasandroid/sleepcloud",
    "image": "https://i.imgur.com/J6jqiOI.png",
    "logoColor": "#d34836",
    "longDescription": "Smart alarm clock with sleep cycle tracking. Wakes you gently in optimal moment for pleasant mornings.",
    "name": "sleepcloud",
    "oauth": true,
    "shortDescription": "Tracks sleep duration and quality.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://accounts.google.com/o/oauth2/auth?access_type=offline&approval_prompt=force&prompt=consent&state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=1052648855194.apps.googleusercontent.com&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Fsleepcloud%2Fconnect&response_type=code&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+openid+profile+email",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "1052648855194.apps.googleusercontent.com",
    "scopes": [
      "https://www.googleapis.com/auth/userinfo.email",
      "openid",
      "profile",
      "email"
    ],
    "oauthServiceName": "Google"
  },
  "tigerview": {
    "id": 89,
    "timestamps": true,
    "mergeOverlappingMeasurements": true,
    "backgroundColor": "#ff8800",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Goals",
    "displayName": "TigerView",
    "enabled": true,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://tigerview.ecusd7.org",
    "image": "https://static.quantimo.do/img/connectors/tigerview.png",
    "logoColor": "#ff0000",
    "longDescription": "Web-based parent access system for student academic performance",
    "name": "tigerview",
    "oauth": false,
    "shortDescription": "Tracks academic performance and student behaviour.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/tigerview/connect",
      "parameters": [
        {
          "displayName": "Username",
          "key": "username",
          "type": "text"
        },
        {
          "displayName": "Password",
          "key": "password",
          "type": "password"
        }
      ],
      "usePopup": false,
      "text": "Enter your credentials"
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ]
  },
  "twitter": {
    "id": 81,
    "timestamps": true,
    "backgroundColor": "#e4405f",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Social Interactions",
    "displayName": "Twitter",
    "enabled": 1,
    "fontAwesome": "fab fa-twitter",
    "getItUrl": "https://twitter.com/",
    "image": "https://help.twitter.com/content/dam/help-twitter/brand/logo.png",
    "logoColor": "#1da1f2",
    "longDescription": "From breaking news and entertainment to sports and politics, get the full story with all the live commentary.",
    "name": "twitter",
    "oauth": true,
    "shortDescription": "Tracks social interaction.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/twitter/connect",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "providesUserProfileForLogin": true,
    "variableNames": [
      "Twitter Status Update"
    ],
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "IY5PzLeb69a1uCwCVlpWTCQ7Y"
  },
  "whatpulse": {
    "id": 3,
    "timestamps": true,
    "backgroundColor": "#29a2b0",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Activities",
    "displayName": "WhatPulse",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://www.whatpulse.org/downloads/",
    "image": "https://i.imgur.com/EEZxqtd.png",
    "logoColor": "#2d2d2d",
    "longDescription": "WhatPulse is a small application that measures your keyboard/mouse usage, down- & uploads and your uptime. It sends these statistics here, to the website, where you can use these stats to analyze your computing life, compete against or with your friends and compare your statistics to other people.",
    "name": "whatpulse",
    "oauth": false,
    "shortDescription": "Tracks keyboard and mouse usage.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/whatpulse/connect",
      "parameters": [
        {
          "displayName": "Username",
          "key": "username",
          "type": "text",
          "helpText": "Enter your Whatpulse username found next to your avatar on the WhatPulse My Stats page at whatpulse.org. Make sure to set the WhatPulse app to pulse daily like so: https://prnt.sc/r75rrq"
        }
      ],
      "usePopup": false,
      "text": "Enter your Whatpulse username found next to your avatar on the WhatPulse My Stats page at whatpulse.org. Make sure to set the WhatPulse app to pulse daily like so: https://prnt.sc/r75rrq"
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "variableNames": [
      "Mouse Clicks",
      "Keystrokes"
    ],
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ]
  },
  "withings": {
    "id": 9,
    "timestamps": true,
    "backgroundColor": "#00afd8",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Physical Activity",
    "displayName": "Withings",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "getItUrl": "https://partners.withings.com/c/71745/58308/583",
    "image": "https://i.imgur.com/GZ7Kw8A.png",
    "logoColor": "#2d2d2d",
    "longDescription": "Withings creates smart products and apps to take care of yourself and your loved ones in a new and easy way. Discover the Withings Pulse, Wi-Fi Body Scale, and Blood Pressure Monitor.",
    "name": "withings",
    "oauth": true,
    "shortDescription": "Tracks sleep, blood pressure, heart rate, weight, temperature, CO2 levels, and physical activity.",
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://account.withings.com/oauth2_user/authorize2?state=eyJ1c2VyX2lkIjo3LCJjbGllbnRfaWQiOiJxdWFudGltb2RvIiwiaW50ZW5kZWRfdXJsIjoiODNhYzc0NmVhOGM1YmEyMTE3NWE1YjE3MzQ1OWZjMTcxNWE1MTkwYnxodHRwczpcL1wvbG9jYWwucXVhbnRpbW8uZG9cL2FjY291bnQ_c3RhdGU9ZXlKMWMyVnlYMmxrSWpveExDSmpiR2xsYm5SZmFXUWlPaUp4ZFdGdWRHbHRiMlJ2SWl3aWFXNTBaVzVrWldSZmRYSnNJam9pYUhSMGNITTZYQzljTDJ4dlkyRnNMbkYxWVc1MGFXMXZMbVJ2WEM5aFkyTnZkVzUwSW4wLSZjb2RlPTQlMkYwQWZnZVh2dlpGVVdQc0F3blF5cHdKSUpscmtBQ3pBcnJ6VmE1eU5hSkRtUi02azlQb3pXNXA1YWxnTHRmSTVMZUhVQzNiZyZzY29wZT1lbWFpbCtwcm9maWxlK29wZW5pZCtodHRwcyUzQSUyRiUyRnd3dy5nb29nbGVhcGlzLmNvbSUyRmF1dGglMkZ1c2VyaW5mby5wcm9maWxlK2h0dHBzJTNBJTJGJTJGd3d3Lmdvb2dsZWFwaXMuY29tJTJGYXV0aCUyRnVzZXJpbmZvLmVtYWlsJmF1dGh1c2VyPTAmaGQ9dGhpbmtieW51bWJlcnMub3JnJnByb21wdD1jb25zZW50JnNlc3Npb25Ub2tlbj1kZW1vJnF1YW50aW1vZG9Vc2VySWQ9MSZxdWFudGltb2RvQ2xpZW50SWQ9cXVhbnRpbW9kbyZhY2Nlc3NUb2tlbj1iMTM2MzYzMjVkYjQxNGZmYmVlODBjZDBhOWRmYmRmODY2YzVkNzNhIn0-&type=web_server&client_id=1534108b00aa0cad4354983295af6f16aa3ed5c2aa9759e796bd1a4212adf5f5&redirect_uri=https%3A%2F%2Flocal.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Fwithings%2Fconnect&response_type=code&scope=user.info%2Cuser.metrics%2Cuser.activity%2Cuser.sleepevents",
      "usePopup": true
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "variableNames": [
      "Body Weight",
      "Fat-Free Mass (FFM) Or Lean Body Mass (LBM)",
      "Fat Ratio",
      "Fat Mass Weight",
      "Blood Pressure (Diastolic - Bottom Number)",
      "Blood Pressure (Systolic - Top Number)",
      "Heart Rate (Pulse)",
      "Daily Step Count",
      "Walk Or Run Distance",
      "Calories Burned"
    ],
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ],
    "connectorClientId": "1534108b00aa0cad4354983295af6f16aa3ed5c2aa9759e796bd1a4212adf5f5",
    "scopes": [
      "user.info",
      "user.metrics",
      "user.activity",
      "user.sleepevents"
    ]
  },
  "worldweatheronline": {
    "id": 13,
    "timestamps": true,
    "backgroundColor": "#1e2023",
    "dataSourceType": "connector",
    "defaultVariableCategoryName": "Environment",
    "displayName": "Weather",
    "enabled": 1,
    "fontAwesome": "fas fa-plug",
    "image": "https://static.quantimo.do/img/variable_categories/environment.png",
    "logoColor": "#2d2d2d",
    "longDescription": "Automatically import temperature, humidity, and ultraviolet light exposure.",
    "name": "worldweatheronline",
    "oauth": false,
    "shortDescription": "Tracks weather.",
    "synonyms": [
      "Weather"
    ],
    "userId": 7,
    "availableOutsideUS": true,
    "connectInstructions": {
      "url": "https://local.quantimo.do/api/v1/connectors/worldweatheronline/connect",
      "parameters": [
        {
          "displayName": "Postal Code",
          "key": "zip",
          "type": "text",
          "placeholder": "Enter your zip code"
        }
      ],
      "usePopup": false,
      "text": "Enter your postal code"
    },
    "importViaApi": true,
    "minimumAllowedSecondsBetweenMeasurements": 86400,
    "platforms": [
      "ios",
      "android",
      "web",
      "chrome"
    ]
  }
}
