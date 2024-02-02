/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

module.exports = {
    //endpoint: 'https://self-hosted.gitlab/api/v4/',
    token: process.env.GITHUB_ACCESS_TOKEN,
    platform: 'github',
    onboardingConfig: {
        extends: ['config:base'],
    },
    repositories: ['mikepsinn/qm-api'],
};
