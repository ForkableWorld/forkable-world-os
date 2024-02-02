function qmExec(command, callback, suppressErrors, lotsOfOutput) {
    var exec = require('child_process').exec;
    var spawn = require('child_process').spawn; // For commands with lots of output resulting in stdout maxBuffer exceeded error
    qmLog.info('executing ' + command);
    if(lotsOfOutput){
        var args = command.split(" ");
        var program = args.shift();
        var ps = spawn(program, args);
        ps.on('exit', function (code, signal) {
            qmLog.info(command + ' exited with ' + 'code '+ code + ' and signal '+ signal);
            if(callback){callback();}
        });
        ps.stdout.on('data', function (data) {qmLog.info(command + ' stdout: ' + data);});
        ps.stderr.on('data', function (data) {qmLog.error(command + '  stderr: ' + data);});
        ps.on('close', function (code) {if (code !== 0) {qmLog.error(command + ' process exited with code ' + code);}});
    } else {
        var my_child_process = exec(command, function (error, stdout, stderr) {
            if (error !== null) {if (suppressErrors) {qmLog.info('ERROR: exec ' + error);} else {qmLog.error('ERROR: exec ' + error);}}
            callback(error, stdout);
        });
        my_child_process.stdout.pipe(process.stdout);
        my_child_process.stderr.pipe(process.stderr);
    }
}

if(typeof window !== "undefined"){ window.qmExec = qmExec;} else {module.exports = qmExec;}
