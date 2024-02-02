var qmLog = require('./qmLog');
var github = require('github-api');
var qmExec = require('./qmExec');
const git = require('simple-git')();
var qmGit = {
    branchName: null,
    isMaster: function () {
        return qmGit.branchName === "master"
    },
    isDevelop: function () {
        if(!qmGit.branchName){
            throw "Branch name not set!"
        }
        return qmGit.branchName === "develop"
    },
    isFeature: function () {
        return qmGit.branchName.indexOf("feature") !== -1;
    },
    getCurrentGitCommitSha: function () {
        if(process.env.SOURCE_VERSION){return process.env.SOURCE_VERSION;}
        try {
            return require('child_process').execSync('git rev-parse HEAD').toString().trim()
        } catch (error) {
            qmLog.info(error);
        }
    },
    accessToken: process.env.GITHUB_ACCESS_TOKEN,
    getCommitMessage: function(callback){
        if(process.env.BUILDPACK_LOG_FILE){
            qmLog.info("Can't get commit on Heroku");
            callback("Can't get commit on Heroku");
            return;
        }
        var commandForGit = 'git log -1 HEAD --pretty=format:%s';
        qmExec(commandForGit, function (error, output) {
            var commitMessage = output.trim();
            qmLog.info("Commit: "+ commitMessage);
            if(callback) {callback(commitMessage);}
        });
    },
    outputCommitMessageAndBranch: function () {
        qmGit.getCommitMessage(function (commitMessage) {
            qmGit.setBranchName(function (branchName) {
                qmLog.info("=====\nBuilding\n" + commitMessage + "\non branch: "+ branchName + "\n=====");
            })
        })
    },
    setBranchName: function (callback) {
        function setBranch(branch, callback) {
            qmGit.branchName = branch.replace('origin/', '');
            qmLog.info('current git branch: ' + qmGit.branchName);
            if (callback) {callback(qmGit.branchName);}
        }
        if (qmGit.getBranchEnv()){
            setBranch(qmGit.getBranchEnv(), callback);
            return;
        }
        try {
            git.branch({}, function (GitError, BranchSummary) {
                if(err){qmLog.error(err); return;}
                setBranch(BranchSummary.current, callback);
            });
        } catch (e) {
            qmLog.info("Could not set branch name because " + e.message);
        }
    },
    getBranchEnv: function () {
        function getNameIfNotHead(envName) {
            if(process.env[envName] && process.env[envName].indexOf("HEAD") === -1){return process.env[envName];}
            return false;
        }
        if(getNameIfNotHead('CIRCLE_BRANCH')){return process.env.CIRCLE_BRANCH;}
        if(getNameIfNotHead('BUDDYBUILD_BRANCH')){return process.env.BUDDYBUILD_BRANCH;}
        if(getNameIfNotHead('TRAVIS_BRANCH')){return process.env.TRAVIS_BRANCH;}
        if(getNameIfNotHead('GIT_BRANCH')){return process.env.GIT_BRANCH;}
    },
    createStatusToCommit: function(statusOptions, callback){
        github.createStatusToCommit(statusOptions, qmGit.getGithubOptions(), callback);
    },
    getGithubOptions: function(){
        return {
            // Required options: git_token, git_repo
            // refer to https://help.github.com/articles/creating-an-access-token-for-command-line-use/
            git_token: process.env.GITHUB_ACCESS_TOKEN,
            // comment into this repo, this pr.
            git_repo: 'curedao/curedao-web-android-chrome-ios-app-template',
            //git_prid: '1',
            // create status to this commit, optional
            git_sha: qmGit.getCurrentGitCommitSha(),
            jshint_status: 'error',       // Set status to error when jshint errors, optional
            jscs_status: 'failure',       // Set git status to failure when jscs errors, optional
            eslint_status: 'error',       // Set git status to error when eslint errors, optional
            // when using github enterprise, optional
            git_option: {
                // refer to https://www.npmjs.com/package/github for more options
                //host: 'github.mycorp.com',
                // You may require this when you using Enterprise Github
                //pathPrefix: '/api/v3'
            },
            // Provide your own jshint reporter, optional
            jshint_reporter: function (E, file) { // gulp stream file object
                // refer to http://jshint.com/docs/reporters/ for E structure.
                return 'Error in ' + E.file + '!';
            },
            // Provide your own jscs reporter, optional
            jscs_reporter: function (E, file) { // gulp stream file object
                // refer to https://github.com/jscs-dev/node-jscs/wiki/Error-Filters for E structure.
                return 'Error in ' + E.filename + '!';
            }
        };
    }
};
if(typeof window !== "undefined"){ window.qmGit = qmGit;} else {module.exports = qmGit;}
