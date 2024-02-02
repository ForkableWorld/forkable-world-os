// load type definitions that come with Cypress module
/// <reference types="cypress" />
// noinspection JSValidateTypes
let selectors = {
  usernameInput: '#username-group > input',
  emailInput: '#email-group > input',
  pw: '#password-group > input',
  pwConfirm: '#password-confirm-group > input',
  registerButton: '#submit-button-group > div > input.btn.btn-primary',
  acceptButton: '#button-approve',
  errorMessageSelector: '#error-messages > li',
}
let OAUTH_APP_ORIGIN = Cypress.env('OAUTH_APP_ORIGIN')
let redirect_uri = OAUTH_APP_ORIGIN + '/auth/quantimodo/callback'
let clientId = 'oauth_test_client'
let datalabPath = '/datalab';
let clientSecret = 'oauth_test_client_secret'
let appDisplayName = 'OAuth Test Client'
let registerPath = `/auth/register`;
let logoutPath = `/auth/logout`;
let userApiPath = "/api/v1/user";
let APP_ENV = Cypress.env('APP_ENV')
let authorizePath = `/oauth/authorize?` +
                    'response_type=code&' +
                    'scope=readmeasurements%20writemeasurements&' +
                    'state=testabcd&'
let baseUrl = Cypress.config('baseUrl')
let OA
function goToIntroPage (clientId) {
  let url = `/#/app/intro?clientId=${clientId}&logout=true`
  cy.visitIonicAndSetApiUrl(url)
  cy.url().should('contain', 'intro')
}
function checkOauthPageAndAccept (appDisplayName) {
  cy.get('#client-name').should('contain', appDisplayName)
  cy.get('#button-deny').should('contain', 'Cancel')
  cy.get('#button-approve').should('contain', 'Accept')
  cy.get('#button-approve').click({ force: true })
}
function assertLoginRedirect(url){
	cy.visit(url)
	cy.url().should('include', registerPath)
}
function logout(){
	cy.log("Logging out")
	cy.visit(logoutPath)
	cy.url().should('include', 'intro')
	//cy.debug()
	//cy.getCookie('intended_url').should('not.exist')
	//cy.getCookie('final_callback_url').should('not.exist')
	cy.getCookie('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d').should('not.exist')
	checkUserApi(false)
	//cy.getCookie('laravel_session').should('not.exist')
	assertLoginRedirect(datalabPath)
	checkUserApi(false)
}
function checkUserApi(loggedIn){
	let url = baseUrl + userApiPath
	cy.log("Checking user API at " + url)
	cy.api({
		method: "GET",
		url: url,
		failOnStatusCode: false,
	}, {failOnStatusCode: false}).then((resp) => {
		// redirect status code is 302
		if(loggedIn){
			expect(resp.status).to.eq(200)
			expect(resp.body).to.have.property("loginName", "testuser")
			expect(resp.body).to.have.property("id", 18535)
		} else {
			expect(resp.status).to.eq(401)
		}
	})
}
describe('OAuth', function () {
  it.skip('Logs into and out of an OAuth test client app', function () {
	  // TODO: Fix this test
    let clientId = 'oauth_test_client'
    goToIntroPage(clientId)
    // cy.disableSpeechAndSkipIntro()
    // //cy.get('#circle-page-title').should('contain', appDisplayName)
    // //cy.get('#skipButtonIntro').click({ force: true })
    // cy.get('#login-button').click({ force: true })
	  cy.visitApi('/oauth/authorize?client_id=' + clientId + "&grant_type=authorization_code&" +
	              `response_type=code&scope=readmeasurements%20writemeasurements` + `
                &state=testabcd&redirect_uri=${encodeURIComponent(redirect_uri)}`)
    cy.login()
	  checkOauthPageAndAccept(appDisplayName)
    //cy.get('#skipButtonWelcome').click({ force: true });
    cy.url().should('include', '/login')
    cy.wait(10000)
    cy.log('We should be at onboarding now')
    cy.url().should('include', '/onboarding')
    //cy.get('#circle-page-title', {timeout: 90000}).should('contain', "Emotions");  // I have no idea why this won't work
    cy.log('Menu hidden on onboarding page so going straight to settings url')
    cy.logOutViaSettingsPage(false)
  })
  it('Logs in and redirects to authorization page', function () {
    let redirectUrl = `${baseUrl}${authorizePath}client_id=${clientId}&client_secret=${clientSecret}`
    cy.visitApi(`/auth/login?redirectTo=${encodeURIComponent(redirectUrl)}`)
    cy.login()
    cy.allowUncaughtException('Unexpected token')
    checkOauthPageAndAccept(appDisplayName)
    cy.allowUncaughtException(null)
  })
  it.skip('Registers a new user with username and password starting from OAuth web app', function () {
	  // TODO: Fix this test
    let baseUrl = Cypress.config('baseUrl')
    cy.log(`baseUrl is ${baseUrl}`)
    goToIntroPage(clientId)
    cy.disableSpeechAndSkipIntro()
    cy.get('#email-sign-up-button').click({ force: true })
	  let d = new Date()
	  let username = `testuser${d.getTime()}`
	  let email = `testuser${d.getTime()}@gmail.com`
	  let password = 'testing123'
    cy.enterNewUserCredentials(username, email, password)
    checkOauthPageAndAccept(appDisplayName)
    cy.wait(2000)
    cy.url().should('contain', 'onboarding')
    cy.logOutViaSettingsPage(false)
    cy.disableSpeechAndSkipIntro()
    cy.get('#email-sign-up-button').should('exist')
  })
  it.only('Registers a new user after redirecting from authorization page)', function () {
	  let d = new Date()
	  let username = `testuser${d.getTime()}`
	  let email = `testuser${d.getTime()}@gmail.com`
	  let password = 'testing123'
    cy.visitApi(`${authorizePath}client_id=${clientId}&client_secret=${clientSecret}&register=true`)
    cy.enterNewUserCredentials(username, email, password)
    cy.get('#client-name').should('contain', appDisplayName)
    checkOauthPageAndAccept(appDisplayName);
    //cy.allowUncaughtException(); // For some reason callback page has errors
    //cy.wait(5000);
    // TODO: Uncomment after 404.html is deployed to production
    cy.url().should('contain', "/auth/quantimodo/callback?code=");
  })
  it('Logs in after redirecting from authorization page', function () {
    cy.visitApi(`${authorizePath}client_id=${clientId}&register=false`)
    cy.login()
    cy.get('#client-name').should('contain', appDisplayName)
    checkOauthPageAndAccept(appDisplayName);
  })
})
