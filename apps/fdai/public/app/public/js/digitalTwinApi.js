"use strict";
// noinspection TypeScriptRedundantGenericType,JSUnusedGlobalSymbols
// noinspection JSUnusedGlobalSymbols
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.getContracts = exports.uploadLifeForceImage = exports.generateDataGemCanvas = exports.generateLifeForceCanvas = exports.generateDataGemImage = exports.generateLifeForceNftImage = exports.slugify = exports.mintNFTForUserVariable = exports.deployNftContract = exports.getContractAddress = exports.getLifeForceScore = exports.getUserVariable = exports.calculateVariableScore = exports.getDataSources = exports.getRequest = exports.updateDataSourceButtonLink = exports.getAccessToken = void 0;
// noinspection JSUnusedGlobalSymbols,TypeScriptRedundantGenericType
// noinspection TypeScriptRedundantGenericType
var axios_1 = require("axios");
var mathjs_1 = require("mathjs");
var qm = require("./qmHelpers.js");
var canvas_1 = require("canvas");
var fs = require("fs");
var fetch = require('node-fetch');
var FormData = require('form-data');
var storage = qm.storage;
function getAccessToken() {
    var queryParams = new URLSearchParams(window.location.search);
    var accessToken = queryParams.get("accessToken");
    if (accessToken) {
        storage.setItem("accessToken", accessToken);
    }
    else {
        accessToken = storage.getItem("accessToken") || null;
    }
    return accessToken && accessToken.length > 0 ? accessToken : null;
}
exports.getAccessToken = getAccessToken;
function updateDataSourceButtonLink(button) {
    if (!button.link) {
        return;
    }
    try {
        var url = new URL(button.link);
        url.searchParams.set("clientId", "quantimodo");
        url.searchParams.set("final_callback_url", window.location.href);
        button.link = url.href;
    }
    catch (error) {
        debugger;
        console.error(error);
        throw error;
    }
}
exports.updateDataSourceButtonLink = updateDataSourceButtonLink;
function getApiOrigin() {
    return process.env.API_ORIGIN || "https://app.quantimo.do";
}
function getApiUrl(path, params) {
    if (path === void 0) { path = ""; }
    var apiOrigin = getApiOrigin();
    var urlObj = new URL(apiOrigin + path);
    urlObj.searchParams.append("clientId", "quantimodo");
    if (params) {
        for (var key in params) {
            urlObj.searchParams.append(key, params[key]);
        }
    }
    return urlObj.href;
}
var getRequest = function (path, params) { return __awaiter(void 0, void 0, void 0, function () {
    var options, accessToken, response;
    return __generator(this, function (_a) {
        switch (_a.label) {
            case 0:
                options = {
                    method: "GET",
                    headers: { Accept: "application/json" },
                };
                accessToken = getAccessToken();
                if (accessToken) {
                    options.headers["Authorization"] = "Bearer ".concat(accessToken);
                }
                return [4 /*yield*/, fetch(getApiUrl(path, params), options)];
            case 1:
                response = _a.sent();
                if (!response.ok) {
                    return [2 /*return*/, { status: 0, result: [] }];
                }
                return [2 /*return*/, response.json()];
        }
    });
}); };
exports.getRequest = getRequest;
var getDataSources = function () { return __awaiter(void 0, void 0, void 0, function () {
    return __generator(this, function (_a) {
        return [2 /*return*/, (0, exports.getRequest)("/api/v3/connectors/list", { final_callback_url: window.location.href })];
    });
}); };
exports.getDataSources = getDataSources;
var SLEEP_EFFICIENCY = "Sleep Efficiency";
var DAILY_STEP_COUNT = "Daily Step Count";
function calculateVariableScore(uv) {
    var lastValue = uv.lastValue;
    if (uv.unitName === "Percent") {
        return lastValue || null;
    }
    var minimumRecordedValue = uv.minimumRecordedValue;
    var maximumRecordedValue = uv.maximumRecordedValue;
    if (!lastValue || !minimumRecordedValue || !maximumRecordedValue) {
        return null;
    }
    return ((lastValue - minimumRecordedValue) / (maximumRecordedValue - minimumRecordedValue)) * 100;
}
exports.calculateVariableScore = calculateVariableScore;
function getUserVariable(variableName) {
    return __awaiter(this, void 0, void 0, function () {
        var data, variable;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0: return [4 /*yield*/, qm.api.getAsync("/api/v1/userVariables", { name: variableName })];
                case 1:
                    data = _a.sent();
                    if (data.length === 0) {
                        return [2 /*return*/, null];
                    }
                    variable = data[0];
                    return [2 /*return*/, variable];
            }
        });
    });
}
exports.getUserVariable = getUserVariable;
function getLifeForceScore() {
    return __awaiter(this, void 0, void 0, function () {
        var scores, variableNames, _i, variableNames_1, variableName, variable, score;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    scores = [50];
                    variableNames = [DAILY_STEP_COUNT, SLEEP_EFFICIENCY];
                    _i = 0, variableNames_1 = variableNames;
                    _a.label = 1;
                case 1:
                    if (!(_i < variableNames_1.length)) return [3 /*break*/, 4];
                    variableName = variableNames_1[_i];
                    return [4 /*yield*/, getUserVariable(variableName)];
                case 2:
                    variable = _a.sent();
                    if (!variable) {
                        return [3 /*break*/, 3];
                    }
                    score = calculateVariableScore(variable);
                    if (score !== null) {
                        scores.push(score);
                    }
                    _a.label = 3;
                case 3:
                    _i++;
                    return [3 /*break*/, 1];
                case 4: return [2 /*return*/, (0, mathjs_1.mean)(scores)];
            }
        });
    });
}
exports.getLifeForceScore = getLifeForceScore;
function getNftPortApiKey() {
    if (!process.env.NFTPORT_API_KEY) {
        throw new Error("NFTPORT_API_KEY not set");
    }
    return process.env.NFTPORT_API_KEY;
}
function getContractAddress() {
    return __awaiter(this, void 0, void 0, function () {
        var options, response, data;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    options = {
                        method: "GET",
                        headers: { "Content-Type": "application/json", Authorization: getNftPortApiKey() },
                    };
                    return [4 /*yield*/, fetch("https://api.nftport.xyz/v0/contracts/transaction_hash?chain=polygon", options)];
                case 1:
                    response = _a.sent();
                    data = response.json();
                    return [2 /*return*/, data];
            }
        });
    });
}
exports.getContractAddress = getContractAddress;
function deployNftContract() {
    return __awaiter(this, void 0, void 0, function () {
        var options, response;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    options = {
                        method: "POST",
                        headers: { "Content-Type": "application/json", Authorization: getNftPortApiKey() },
                        body: {
                            "chain": "polygon",
                            "name": "Polypunks",
                            "symbol": "PP",
                            "owner_address": "Your wallet address here",
                            "metadata_updatable": true
                        }
                    };
                    return [4 /*yield*/, fetch("https://api.nftport.xyz/v0/contracts", options)];
                case 1:
                    response = _a.sent();
                    return [2 /*return*/, getContractAddress()];
            }
        });
    });
}
exports.deployNftContract = deployNftContract;
function mintNFTForUserVariable(recipientAddress, userVariable) {
    return __awaiter(this, void 0, void 0, function () {
        var form, data, key, options;
        return __generator(this, function (_a) {
            form = new FormData();
            form.append("file", "");
            data = JSON.parse(JSON.stringify(userVariable));
            data.image = generateVariableNftImage(userVariable.name);
            debugger;
            key = process.env.REACT_APP_NFTPORT_API_KEY;
            if (!key) {
                throw new Error("Please set REACT_APP_NFTPORT_API_KEY to create NFTs");
            }
            options = {
                method: "POST",
                url: "https://api.nftport.xyz/v0/mints/easy/urls",
                params: {
                    chain: "polygon",
                    description: "A JSON file containing " + userVariable.name + " Data",
                    mint_to_address: recipientAddress,
                    name: userVariable.name + " Data",
                    file_url: getApiUrl("/api/v3/variables", { accessToken: getAccessToken() }),
                },
                headers: {
                    "Content-Type": "application/json",
                    Authorization: key,
                },
                data: form,
            };
            return [2 /*return*/, axios_1.default.request(options)];
        });
    });
}
exports.mintNFTForUserVariable = mintNFTForUserVariable;
var width = 1264;
var height = 1264;
var titleFont = "50pt Comic Sans MS";
var scoreFont = "30pt Comic Sans MS";
var slugify = function (string) {
    return string
        .toLowerCase()
        .replace(/ /g, "-")
        .replace(/[^\w-]+/g, "");
};
exports.slugify = slugify;
function addEnergyBars(context, numberOfRectangles) {
    // Add Energy Bar
    context.fillStyle = '#58378C';
    context.fillRect(441.55, 1041.95, (651 / 100) * numberOfRectangles, 68);
}
function addTitleText(context, variableName) {
    // Add Title Text
    context.font = titleFont;
    context.textBaseline = 'top';
    context.fillStyle = 'black';
    context.fillText(variableName, 61, 28);
}
function addScoreText(context, variableName) {
    // Add Score Text
    context.font = scoreFont;
    context.fillText(variableName, 400, 948);
}
function addBoxText(context, text) {
    var boxFont = "60pt Comic Sans MS";
    context.font = boxFont;
    context.fillText(text, 140, 970);
}
function addScoreImage(url, context) {
    return __awaiter(this, void 0, void 0, function () {
        var smallImageData;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0: return [4 /*yield*/, (0, canvas_1.loadImage)(url)];
                case 1:
                    smallImageData = _a.sent();
                    context.drawImage(smallImageData, 113.01, 922.06, 220.7, 220.7);
                    return [2 /*return*/];
            }
        });
    });
}
function addBackgroundImage(canvas, backgroundImg) {
    return __awaiter(this, void 0, void 0, function () {
        var backgroundImage, context;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    if (!backgroundImg) {
                        backgroundImg = 'https://static.quantimo.do/humanfs/human-fs-nft-background.png';
                    }
                    return [4 /*yield*/, (0, canvas_1.loadImage)(backgroundImg)
                        // @ts-ignore
                    ];
                case 1:
                    backgroundImage = _a.sent();
                    // @ts-ignore
                    if (typeof backgroundImg.image !== 'undefined') {
                        // @ts-ignore
                        backgroundImage = backgroundImg.image;
                    }
                    context = canvas.getContext('2d');
                    context.drawImage(backgroundImage, 0, 0, width, height);
                    return [2 /*return*/, context];
            }
        });
    });
}
function generateVariableNftImage(variableName, score, backgroundImg) {
    return __awaiter(this, void 0, void 0, function () {
        var canvas, variable, context;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    canvas = (0, canvas_1.createCanvas)(width, height);
                    return [4 /*yield*/, getUserVariable(variableName)];
                case 1:
                    variable = _a.sent();
                    if (!variable) {
                        throw new Error('Could not find variable named ' + variableName);
                    }
                    if (!score) {
                        score = calculateVariableScore(variable);
                    }
                    return [4 /*yield*/, addBackgroundImage(canvas, backgroundImg)];
                case 2:
                    context = _a.sent();
                    if (!score) return [3 /*break*/, 4];
                    return [4 /*yield*/, addScoreImage(variable.url, context)];
                case 3:
                    _a.sent();
                    addEnergyBars(context, score);
                    _a.label = 4;
                case 4:
                    addTitleText(context, variableName);
                    addScoreText(context, variableName);
                    return [2 /*return*/, canvas.toDataURL('image/png')];
            }
        });
    });
}
function generateLifeForceNftImage(backgroundImg) {
    return __awaiter(this, void 0, void 0, function () {
        var canvas, data, str, buffer;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0: return [4 /*yield*/, generateLifeForceCanvas(backgroundImg)];
                case 1:
                    canvas = _a.sent();
                    data = canvas.toDataURL('image/png');
                    str = data.toString();
                    buffer = canvas.toBuffer("image/png");
                    fs.writeFileSync("./digital-twin.png", buffer);
                    return [2 /*return*/, str];
            }
        });
    });
}
exports.generateLifeForceNftImage = generateLifeForceNftImage;
function generateDataGemImage(backgroundImg) {
    return __awaiter(this, void 0, void 0, function () {
        var canvas, data, str, buffer;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0: return [4 /*yield*/, generateLifeForceCanvas(backgroundImg)];
                case 1:
                    canvas = _a.sent();
                    data = canvas.toDataURL('image/png');
                    str = data.toString();
                    buffer = canvas.toBuffer("image/png");
                    fs.writeFileSync("./data-gem.png", buffer);
                    return [2 /*return*/, str];
            }
        });
    });
}
exports.generateDataGemImage = generateDataGemImage;
function generateLifeForceCanvas(backgroundImg) {
    return __awaiter(this, void 0, void 0, function () {
        var canvas, context, lifeForceScore;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    canvas = (0, canvas_1.createCanvas)(width, height);
                    return [4 /*yield*/, addBackgroundImage(canvas, backgroundImg)];
                case 1:
                    context = _a.sent();
                    return [4 /*yield*/, getLifeForceScore()];
                case 2:
                    lifeForceScore = _a.sent();
                    addEnergyBars(context, lifeForceScore);
                    addTitleText(context, 'Your Digital Twin');
                    addScoreText(context, 'Life Force Score');
                    lifeForceScore = Math.round(lifeForceScore);
                    addBoxText(context, lifeForceScore + '%');
                    return [2 /*return*/, canvas];
            }
        });
    });
}
exports.generateLifeForceCanvas = generateLifeForceCanvas;
function generateDataGemCanvas(variableName, backgroundImg) {
    return __awaiter(this, void 0, void 0, function () {
        var canvas, context, lifeForceScore;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    canvas = (0, canvas_1.createCanvas)(width, height);
                    return [4 /*yield*/, addBackgroundImage(canvas, backgroundImg)];
                case 1:
                    context = _a.sent();
                    return [4 /*yield*/, getLifeForceScore()];
                case 2:
                    lifeForceScore = _a.sent();
                    addEnergyBars(context, lifeForceScore);
                    addTitleText(context, variableName + ' Data Gem');
                    addScoreText(context, 'Life Force Score');
                    lifeForceScore = Math.round(lifeForceScore);
                    addBoxText(context, lifeForceScore + '%');
                    return [2 /*return*/, canvas];
            }
        });
    });
}
exports.generateDataGemCanvas = generateDataGemCanvas;
function uploadLifeForceImage() {
    return __awaiter(this, void 0, void 0, function () {
        var imageData, form, fileStream, options, response, data;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0: return [4 /*yield*/, generateLifeForceNftImage()];
                case 1:
                    imageData = _a.sent();
                    fs.writeSync(fs.openSync('image.png', 'w'), imageData);
                    form = new FormData();
                    fileStream = fs.createReadStream('image.png');
                    form.append('file', fileStream);
                    options = {
                        method: 'POST',
                        body: form,
                        headers: {
                            'Authorization': getNftPortApiKey(),
                        },
                    };
                    return [4 /*yield*/, fetch('https://api.nftport.xyz/v0/files', options)];
                case 2:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 3:
                    data = _a.sent();
                    return [2 /*return*/, data];
            }
        });
    });
}
exports.uploadLifeForceImage = uploadLifeForceImage;
function getContracts() {
    return __awaiter(this, void 0, void 0, function () {
        return __generator(this, function (_a) {
            return [2 /*return*/];
        });
    });
}
exports.getContracts = getContracts;
