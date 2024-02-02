"use strict";
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
        while (_) try {
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
exports.listFilesRecursively = exports.uploadFolderToS3 = exports.download = exports.getAbsolutePath = exports.writeToFile = exports.uploadToS3 = exports.uploadToS3InSubFolderWithCurrentDateTime = exports.downloadFromS3 = exports.getS3Client = exports.deleteFile = exports.createFile = exports.exists = exports.assertExists = exports.assertDoesNotExist = void 0;
// noinspection JSUnusedGlobalSymbols,JSUnusedGlobalSymbols
var aws_sdk_1 = require("aws-sdk");
var fs = require("fs");
var https = require("https");
var mime = require("mime");
var path = require("path");
var Q = require("q");
var rimraf_1 = require("rimraf");
var env_helper_1 = require("./env-helper");
var qmLog = require("./qm.log");
var defaultS3Bucket = "qmimages";
// tslint:disable-next-line:no-var-requires
var appRoot = require("app-root-path");
function assertDoesNotExist(relative) {
    var abs = getAbsolutePath(relative);
    if (fs.existsSync(abs)) {
        throw Error(abs + " exists!");
    }
}
exports.assertDoesNotExist = assertDoesNotExist;
function assertExists(relative) {
    var abs = getAbsolutePath(relative);
    if (!fs.existsSync(abs)) {
        throw Error(abs + " does not exist!");
    }
}
exports.assertExists = assertExists;
// require untyped library file
// tslint:disable-next-line:no-var-requires
var qm = require("../public/js/qmHelpers.js");
function exists(filename) {
    var filepath = getAbsolutePath(filename);
    return fs.existsSync(filepath);
}
exports.exists = exists;
function createFile(filePath, contents) {
    return writeToFile(filePath, contents);
}
exports.createFile = createFile;
function deleteFile(filename) {
    var deferred = Q.defer();
    var filepath = getAbsolutePath(filename);
    (0, rimraf_1.default)(filepath, function () {
        qmLog.info(filepath + "\n\tdeleted!");
        deferred.resolve();
    });
    return deferred.promise;
}
exports.deleteFile = deleteFile;
function getS3Client() {
    var s3Options = {
        accessKeyId: (0, env_helper_1.getEnvOrException)([env_helper_1.envNames.QM_STORAGE_ACCESS_KEY_ID, env_helper_1.envNames.STORAGE_ACCESS_KEY_ID]),
        secretAccessKey: (0, env_helper_1.getEnvOrException)([env_helper_1.envNames.QM_STORAGE_SECRET_ACCESS_KEY, env_helper_1.envNames.STORAGE_SECRET_ACCESS_KEY]),
    };
    return new aws_sdk_1.default.S3(s3Options);
}
exports.getS3Client = getS3Client;
function downloadFromS3(filePath, key, bucketName) {
    if (bucketName === void 0) { bucketName = defaultS3Bucket; }
    var s3 = getS3Client();
    var deferred = Q.defer();
    s3.getObject({
        Bucket: bucketName,
        Key: key,
    }, function (err, data) {
        if (err) {
            if (err.name === "NoSuchKey") {
                console.warn(key + " not found in bucket: " + bucketName);
                deferred.resolve(null);
                return;
            }
            throw err;
        }
        if (data && data.Body) {
            fs.writeFileSync(filePath, data.Body.toString());
            console.log("".concat(filePath, " has been created!"));
            deferred.resolve(filePath);
        }
        else {
            throw Error(key + " not found in bucket: " + bucketName);
        }
    });
    return deferred.promise;
}
exports.downloadFromS3 = downloadFromS3;
function uploadToS3InSubFolderWithCurrentDateTime(relative, s3BasePath, s3Bucket, accessControlLevel, ContentType) {
    if (s3Bucket === void 0) { s3Bucket = defaultS3Bucket; }
    if (accessControlLevel === void 0) { accessControlLevel = "public-read"; }
    var at = new Date();
    var dateTime = at.toISOString();
    return uploadToS3(relative, s3BasePath + "/" + dateTime + "/" + relative, s3Bucket, accessControlLevel, ContentType);
}
exports.uploadToS3InSubFolderWithCurrentDateTime = uploadToS3InSubFolderWithCurrentDateTime;
function uploadToS3(filePath, s3Key, s3Bucket, accessControlLevel, ContentType) {
    if (s3Bucket === void 0) { s3Bucket = defaultS3Bucket; }
    if (accessControlLevel === void 0) { accessControlLevel = "public-read"; }
    var deferred = Q.defer();
    var s3 = getS3Client();
    var abs = getAbsolutePath(filePath);
    assertExists(abs);
    var fileContent = fs.readFileSync(abs);
    var params = {
        ACL: accessControlLevel,
        Body: fileContent,
        Bucket: s3Bucket,
        Key: s3Key,
    };
    if (!ContentType) {
        try {
            ContentType = mime.getType(s3Key);
        }
        catch (e) {
            qmLog.error(e);
        }
    }
    if (ContentType) {
        // @ts-ignore
        params.ContentType = ContentType;
    }
    qmLog.info("\n\tuploading to s3: " + s3Key);
    s3.upload(params, function (err, SendData) {
        if (err) {
            qmLog.error(s3Key + "\n\t FAILED to uploaded");
            deferred.reject(err);
        }
        else {
            qmLog.info(s3Key + "\n\tuploaded to\t\n" + SendData.Location);
            deferred.resolve(SendData.Location);
        }
    });
    return deferred.promise;
}
exports.uploadToS3 = uploadToS3;
function writeToFile(filePath, contents) {
    var deferred = Q.defer();
    function ensureDirectoryExistence(filePathToCheck) {
        var dirname = path.dirname(filePathToCheck);
        if (fs.existsSync(dirname)) {
            return true;
        }
        ensureDirectoryExistence(dirname);
        fs.mkdirSync(dirname);
    }
    // tslint:disable-next-line:prefer-const
    var absolutePath = getAbsolutePath(filePath);
    ensureDirectoryExistence(absolutePath);
    qmLog.info("Writing to " + absolutePath);
    fs.writeFile(absolutePath, contents, function (err) {
        if (err) {
            deferred.reject(err);
        }
        // tslint:disable-next-line:no-console
        qmLog.info(absolutePath + " saved!");
        deferred.resolve(absolutePath);
    });
    return deferred.promise;
}
exports.writeToFile = writeToFile;
function getAbsolutePath(relativePath) {
    if (path.isAbsolute(relativePath)) {
        return relativePath;
    }
    else {
        var absPath = path.resolve(appRoot.path, relativePath);
        if (!fs.existsSync(absPath)) {
            absPath = path.resolve(__dirname, relativePath);
        }
        if (!fs.existsSync(absPath)) {
            console.error("Could not find relativePath: ".concat(relativePath, " in appRoot.path ").concat(appRoot.path, " or __dirname: ").concat(__dirname) +
                " path.resolve(__dirname, relativePath) = " + path.resolve(__dirname, relativePath));
        }
        return absPath;
    }
}
exports.getAbsolutePath = getAbsolutePath;
function download(url, relative) {
    var deferred = Q.defer();
    var absolutePath = getAbsolutePath(relative);
    var file = fs.createWriteStream(absolutePath);
    qmLog.info("Downloading " + url + " to " + absolutePath + "...");
    https.get(url, function (response) {
        response.pipe(file);
        file.on("finish", function () {
            file.on("close", function () {
                deferred.resolve(absolutePath);
            });
            file.close();
        });
    });
    return deferred.promise;
}
exports.download = download;
function uploadFolderToS3(dir, s3BasePath, s3Bucket, accessControlLevel, ContentType) {
    if (s3Bucket === void 0) { s3Bucket = defaultS3Bucket; }
    if (accessControlLevel === void 0) { accessControlLevel = "public-read"; }
    return __awaiter(this, void 0, void 0, function () {
        var files, urls, _i, files_1, file, dirWithForwardSlashes, fileWithForwardSlashes, relativePath, s3Key, _a, _b;
        return __generator(this, function (_c) {
            switch (_c.label) {
                case 0: return [4 /*yield*/, listFilesRecursively(dir)];
                case 1:
                    files = _c.sent();
                    qmLog.info("Uploading " + files.length + " files to " + s3Bucket + "/" + s3BasePath + "...");
                    urls = [];
                    _i = 0, files_1 = files;
                    _c.label = 2;
                case 2:
                    if (!(_i < files_1.length)) return [3 /*break*/, 5];
                    file = files_1[_i];
                    dirWithForwardSlashes = qm.stringHelper.replaceBackSlashes(dir, "/");
                    fileWithForwardSlashes = qm.stringHelper.replaceBackSlashes(file, "/");
                    relativePath = fileWithForwardSlashes.replace(dirWithForwardSlashes, "");
                    s3Key = s3BasePath + relativePath;
                    s3Key = s3Key.replace("\\", "/");
                    _b = (_a = urls).push;
                    return [4 /*yield*/, uploadToS3(file, s3Key, s3Bucket, ContentType)];
                case 3:
                    _b.apply(_a, [_c.sent()]);
                    _c.label = 4;
                case 4:
                    _i++;
                    return [3 /*break*/, 2];
                case 5: return [2 /*return*/, urls];
            }
        });
    });
}
exports.uploadFolderToS3 = uploadFolderToS3;
function listFilesRecursively(dirPath, arrayOfFiles) {
    return __awaiter(this, void 0, void 0, function () {
        var fileNames, _i, fileNames_1, fileName, dirOrFile, filePath;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    fileNames = fs.readdirSync(dirPath);
                    arrayOfFiles = arrayOfFiles || [];
                    _i = 0, fileNames_1 = fileNames;
                    _a.label = 1;
                case 1:
                    if (!(_i < fileNames_1.length)) return [3 /*break*/, 5];
                    fileName = fileNames_1[_i];
                    dirOrFile = path.join(dirPath, fileName);
                    if (!fs.statSync(dirOrFile).isDirectory()) return [3 /*break*/, 3];
                    return [4 /*yield*/, listFilesRecursively(dirOrFile, arrayOfFiles)];
                case 2:
                    arrayOfFiles = _a.sent();
                    return [3 /*break*/, 4];
                case 3:
                    filePath = path.join(dirPath, fileName);
                    arrayOfFiles.push(filePath);
                    _a.label = 4;
                case 4:
                    _i++;
                    return [3 /*break*/, 1];
                case 5: return [2 /*return*/, arrayOfFiles];
            }
        });
    });
}
exports.listFilesRecursively = listFilesRecursively;
