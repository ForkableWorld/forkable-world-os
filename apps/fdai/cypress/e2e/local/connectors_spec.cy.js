// load type definitions that come with Cypress module
function getConnectorTestUsername(connectorName){
	let testUserVarName = "CONNECTOR_" + connectorName.toUpperCase() + "_TEST_USERNAME"
	let username = Cypress.env(testUserVarName)
	if(!username){throw "Please set env " + testUserVarName}
	return username
}
function getConnectorTestPassword(connectorName){
	let passwordVarName = "CONNECTOR_" + connectorName.toUpperCase() + "_TEST_PASSWORD"
	return Cypress.env(passwordVarName)
}
function getLoginUrl(){
	return Cypress.config('baseUrl') + "/auth/login"
}
/// <reference types="cypress" />
describe("Mobile Connectors", function(){
	let selectors = {
		fitbit: {
			username: "#ember654",
			password: "#ember655",
			submitButton: "#ember695",
		},
		rescuetime: {
			username: "#email",
			password: "#password",
			submitButton: "button[name=\"button\"]",
		},
		facebook: {
			username: "#email",
			password: "#pass",
			submitButton: "#u_0_2",
		},
		github: {
			username: "#login_field",
			password: "#password",
			submitButton: "#login > div.auth-form-body.mt-3 > form > div > input.btn.btn-primary.btn-block.js-sign-in-button",
		},
		runkeeper: {
			username: "#lightBoxLogInForm > input[id=\"emailInput\"][name=\"email\"]",
			password: "#lightBoxLogInForm > input[id=\"passwordInput\"][name=\"password\"]",
			submitButton: "#lightBoxLogInForm > button[id=\"loginSubmit\"][type=\"submit\"].component.ctaButton.primary.medium.signup_email_button.success.small.expand > .buttonText",
		},
	}
	function mobileConnectPageWithAuth(){
		mobileConnectPage(`?log=testuser&pwd=testing123`)
	}
	function mobileConnectPage(query){
		query = query || ""
		cy.visitApi(`/api/v1/connect/mobile${query}`)
		cy.get("body", {timeout: 10000}).should("contain", "Google")
	}
	/**
	 * @param {string} connectorName
	 */
	function clickConnect(connectorName){
		cy.log(`Connecting ${connectorName}`)
		cy.get(`#${connectorName}-connect-button`, {timeout: 10000}).click()
		//cy.wait(5000)
	}
	/**
	 * @param {string} connectorName
	 */
	function clickDisconnect(connectorName){
		cy.log(`Disconnecting ${connectorName}`)
		cy.get(`#${connectorName}-disconnect-button`).click()
	}
	/**
	 * @param {string} connectorName
	 */
	function disconnectAndClickConnect(connectorName){
		mobileConnectPageWithAuth()
		// this only works if there's 100% guarantee body has fully rendered without any pending changes to its state
		cy.get(`#${connectorName} > div.qm-account-block-right > div.qm-button-container`,
			{timeout: 20000}).then(($connectorBlock) => {
			//debugger
			// synchronously ask for the body's text and do something based on whether it includes another string
			if($connectorBlock.text().includes("Disconnect")){
				clickDisconnect(connectorName)
				clickConnect(connectorName)
			} else {
				cy.log(`${connectorName} already disconnected`)
				clickConnect(connectorName)
			}
		})
	}
	/**
	 * @param {string} connectorName
	 */
	function enterCredentials(connectorName){
		cy.log(`=== enterCredentials for ${connectorName} ===`)
		cy.get(selectors[connectorName].username)
		  .click({force: true})
		  .type(getConnectorTestUsername(connectorName), {force: true})
		cy.get(selectors[connectorName].password)
		  .click({force: true})
		  .type(getConnectorTestPassword(connectorName), {force: true})
	}
	/**
	 * @param {string} connectorName
	 */
	function clickSubmit(connectorName){
		cy.log(`=== clickSubmit for ${connectorName} ===`)
		let submitButton = cy.get(selectors[connectorName].submitButton)
		submitButton.click({force: true});
	}
	/**
	 * @param {string} connectorName
	 */
	function verifyConnection(connectorName){
		cy.get(`#${connectorName}-scheduled-button`, {timeout: 60000})
		  .should("contain", "Update Scheduled")
		cy.get(`#${connectorName}-disconnect-button`)
		  .should("exist")
		cy.get(`#${connectorName}-connect-button`)
		  .should("not.exist")
	}

	it.skip('Login through Google', () => { // TODO: fix this test
		const username = getConnectorTestUsername('google')
		const password = getConnectorTestPassword('google')
		const loginUrl = Cypress.env('loginUrl') || getLoginUrl()
		const cookieName =  'laravel_session' || Cypress.env('cookieName')
		const socialLoginOptions = {
			username: username,
			password: password,
			loginUrl: loginUrl,
			headless: false,
			logs: true,
			loginSelector: '#google-login-button',
			postLoginSelector: '.account-panel'
		}

		return cy.task('GoogleSocialLogin', socialLoginOptions).then(({cookies}) => {
			cy.clearCookies()

			const cookie = cookies.filter(cookie => cookie.name === cookieName).pop()
			if (cookie) {
				cy.setCookie(cookie.name, cookie.value, {
					domain: cookie.domain,
					expiry: cookie.expires,
					httpOnly: cookie.httpOnly,
					path: cookie.path,
					secure: cookie.secure
				})

				Cypress.Cookies.defaults({
					preserve: cookieName
				})
			}
		})
	})
	it("Gets connector list without authentication", function(){
		mobileConnectPage()
		cy.get("body").should("not.contain", "Fitbit")
	})
	it("Gets connector list with credentials in URL", function(){
		mobileConnectPageWithAuth()
		cy.get("body").should("contain", "Fitbit")
	})
	it("Connects and disconnects Fitbit", function(){
		// This causes an infinite loop of redirects in the test but works in real life
		let connectorName = "fitbit"
		disconnectAndClickConnect(connectorName)
		cy.url().should("contain", "https://www.fitbit.com/")
		//enterCredentials(connectorName)  Selector changes too much
		// Causes infinite loop of redirects in test but works in real life
		//clickSubmit(connectorName)
		//verifyConnection(connectorName)
		//clickDisconnect(connectorName)
	})
	// TODO: Re-enable when Whatpulse is fixed
	it("Connects and disconnects WhatPulse", function(){
		disconnectAndClickConnect("whatpulse")
		cy.get("input[name=\"username\"]").type("mikepsinn", {force: true})
		cy.get(".qm-account-block[data-name=whatpulse] .qm-account-connect-button-with-params")
		  .click({force: true})
		verifyConnection("whatpulse")
		clickDisconnect("whatpulse")
	})
	// TODO: Moodpanda doesn't have an API anymore, I think
	it.skip("Connects and disconnects MoodPanda", function(){
		disconnectAndClickConnect("moodpanda")
		cy.get("input[name=\"email\"]").type(getConnectorTestUsername('moodpanda'), {force: true})
		cy.get(".qm-account-block[data-name=moodpanda] .qm-account-connect-button-with-params")
		  .click({force: true})
		verifyConnection("moodpanda")
		clickDisconnect("moodpanda")
	})
	it("Connects and disconnects GitHub", function(){
		let connectorName = "github"
		disconnectAndClickConnect(connectorName)
		enterCredentials(connectorName)
		//clickSubmit(connectorName) // I get too many emails from GitHub
		//verifyConnection(connectorName)
		//clickDisconnect(connectorName)
		//cy.get("body").should("contain", "verification")
	})
	it("Connects and disconnects Facebook", function(){
		let connectorName = "facebook"
		disconnectAndClickConnect(connectorName)
		enterCredentials(connectorName)
		//clickSubmit(connectorName)
		//verifyConnection(connectorName)
		//clickDisconnect(connectorName)
	})
	it.skip("Connects and disconnects RunKeeper", function(){
		cy.log("=== TODO: Fix RunKeeper ===")
		let connectorName = "runkeeper"
		disconnectAndClickConnect(connectorName)
		enterCredentials(connectorName)
		clickSubmit(connectorName)
		//verifyConnection(connectorName)
		//clickDisconnect(connectorName)
	})
	it.skip("Connects and disconnects Rescuetime", function(){
		// We don't have a client for any except app.quantimo.do
		//cy.visit('https://www.rescuetime.com/logout');
		let connectorName = "rescuetime"
		disconnectAndClickConnect(connectorName)
		enterCredentials(connectorName)
		clickSubmit(connectorName)
		verifyConnection(connectorName)
		clickDisconnect(connectorName)
	})
	it('Logs in via Facebook', function () {
		// TODO: Fix me because I just lead so a broken cypress page and can't even see other test results
		cy.visitApi(`/auth/login`)
		cy.get('body > div.container > div > div.panel-body.social > div:nth-child(1) > a')
		  .click({ force: true })
		enterCredentials('facebook')
		// TODO: Fix me because I just lead so a broken cypress page and can't even see other test results
		// cy.get('#skipButtonIntro').click({ force: true })
		// cy.log('Wont be there if user has not upgraded')
		// cy.get('#navBarAvatar').click({ force: true })
	})
})
