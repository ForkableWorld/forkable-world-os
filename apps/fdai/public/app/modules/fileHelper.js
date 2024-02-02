import * as Q from "q";
import path from "path";
import fs from "fs";
import * as qmLog from "../ts/qm.log";
import {getAbsolutePath} from "../ts/qm.file-helper";

export function writeToFile(filePath, contents) {
    const deferred = Q.defer()
    function ensureDirectoryExistence(filePathToCheck) {
        const dirname = path.dirname(filePathToCheck)
        if (fs.existsSync(dirname)) {
            return true
        }
        ensureDirectoryExistence(dirname)
        fs.mkdirSync(dirname)
    }

    // tslint:disable-next-line:prefer-const
    let absolutePath = getAbsolutePath(filePath)
    ensureDirectoryExistence(absolutePath)
    qmLog.info("Writing to " + absolutePath)
    fs.writeFile(absolutePath, contents, (err) => {
        if (err) {
            deferred.reject(err)
        }
        // tslint:disable-next-line:no-console
        qmLog.info(absolutePath + " saved!")
        deferred.resolve(absolutePath)
    })
    return deferred.promise
}
