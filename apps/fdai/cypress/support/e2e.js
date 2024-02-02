// ***********************************************************
// This example support/index.js is processed and loaded automatically before your test files.
// This is a great place to put global configuration and behavior that modifies Cypress.
// You can change the location of this file or turn off automatically serving support files with the 'supportFile'
// configuration option. You can read more here: https://on.cypress.io/configuration
// ***********************************************************

import './commands' // Import commands.js using ES2015 syntax:
import "cypress-fail-fast";
import 'cypress-plugin-api';
import "cypress-localstorage-commands";
// eslint-disable-next-line no-unused-vars
// noinspection JSUnusedLocalSymbols
var allowLogging = false // For some reason cypress always logs in staging even if env isn't set
Cypress.on('uncaught:exception', (err, runnable) => {
    if(err.message.indexOf('runnable must have an id') !== false){
        cy.log(err.message)
        return false
    }
    let expectedErrorMessage = Cypress.env('expectedErrorMessage')
    if(expectedErrorMessage){
        expect(err.message).to.include(expectedErrorMessage)
        return false
    }
    cy.log(`Uncaught exception: ${err.message}`)
})
beforeEach(function(){ // runs before each test in the block
    let url = Cypress.config('baseUrl')
    if(!url){
        debugger
        throw Error("baseUrl not set!")
    }
    cy.log(`baseUrl is ${url}`)
	Cypress.Cookies.debug(true)
	cy.setCookie('XDEBUG_SESSION', 'PHPSTORM')
})
import addContext from 'mochawesome/addContext'
Cypress.on('test:after:run', (test, runnable) => {
    // https://medium.com/@nottyo/generate-a-beautiful-test-report-from-running-tests-on-cypress-io-371c00d7865a
    if(test.state === 'failed'){
        let specName = Cypress.spec.name
        let runnableTitle = runnable.parent.title
        let testTitle = test.title
        const screenshotFileName = `${runnableTitle} -- ${testTitle} (failed).png`
        const folder = Cypress.config('screenshotsFolder') + `/${specName}/`
        const screenshotPath = folder + screenshotFileName
        //const screenshotFileName =  `./${specName}/${runnableTitle.replace(':', '')} -- ${testTitle} (failed).png`
        console.error(`screenshotPath ${screenshotPath}`)
        addContext({test}, screenshotPath)
    }
})

// Hide fetch/XHR requests from command log
if (Cypress.env('hideXHRInCommandLog')) {
	const app = window.top;
	if (
		app &&
		!app.document.head.querySelector('[data-hide-command-log-request]')
	) {
		const style = app.document.createElement('style');
		style.innerHTML =
			'.command-name-request, .command-name-xhr { display: none }';
		style.setAttribute('data-hide-command-log-request', '');

		app.document.head.appendChild(style);
	}
}

beforeEach(() => {
    //cy.server()
	// 👇 Didn't work 👇 Had to disable rocket loader in cloudflare https://dash.cloudflare.com/52e6cea8444378116bd4a9c8834e1b27/quantimo.do/speed/optimization
	// cy.intercept('GET', '/cdn-cgi/scripts/*/cloudflare-static/rocket-loader.min.js*', req => {
	// 	req.reply({ statusCode: 404, body: 'Cypress forced 404', });
	// 	req.statusCode = 404
	// });
    cy.intercept('GET', '/api/v3/measurements*').as('measurements')
    cy.intercept('GET', '/api/v3/variables*').as('get-variables')
    cy.intercept('POST', '/api/v3/measurements*').as('post-measurement')
    cy.intercept('POST', '/api/v3/measurements/delete').as('delete-measurements')
    cy.intercept('POST', '/api/v3/trackingReminderNotifications*').as('post-notifications')
	cy.intercept('GET', '/api/v1/user').as('get-user')
})
