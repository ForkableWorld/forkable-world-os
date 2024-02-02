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
let datalabPath = '/datalab';
let testUserName = 'testuser'
let testUserPassword = 'testing123'
let registerPath = `/auth/register`;
let APP_ENV = Cypress.env('APP_ENV')
let baseUrl = Cypress.config('baseUrl')
let LOGIN_COOKIE_NAME = 'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'
function assertLoginCookieNotPresent(){
	cy.log("Checking login cookie is not present")
	cy.getCookie(LOGIN_COOKIE_NAME).should("not.exist")
}
function assertLocalStorage(key, value){
	cy.log(`Checking local storage ${key} is ${value}`)
	cy.getLocalStorage("cookies-accepted").should("equal", null);
}
function assertAccessTokenNotInLocalStorage(){
	cy.log("Checking access token is not present")
	assertLocalStorage('accessToken', null)
}
function assertUserNotLoggedIn(){
	cy.log("Checking user is not logged in")
	assertLoginCookieNotPresent()
	assertAccessTokenNotInLocalStorage()
	checkUserApi(false)
}
function logout(){
	cy.log("Logging out")
	cy.visit(`/auth/logout`)
	cy.url().should('include', 'intro')
	//cy.debug()
	//cy.getCookie('intended_url').should('not.exist')
	//cy.getCookie('final_callback_url').should('not.exist')
	assertUserNotLoggedIn()
	//cy.getCookie('laravel_session').should('not.exist')
	cy.visit(datalabPath)
	cy.url().should('include', registerPath)
	assertUserNotLoggedIn()
}
function checkUserApi(loggedIn){
	let url = baseUrl + "/api/v1/user"
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
function assertDatalabRedirectsToRegisterPage(){
	cy.log("Checking datalab redirects to register page")
	cy.visit(datalabPath)
	return cy.url().should("include", baseUrl + registerPath)
}
function goToInbox(){
	cy.log("Going to inbox")
	return cy.visit("app/public/#/app/reminders-inbox")
}
function skipIntro(){
	cy.disableSpeechAndSkipIntro()
}
function assertOnLoginPage(){
	cy.log("Checking on login page")
	return cy.url().should("contain", "login")
}
function asserUrlContains(needle){
	cy.log("Checking URL contains " + needle)
	cy.url({timeout: 10000}).should("contain", needle)
}
describe('Authentication', function () {
	it('Logs in and out with username and password', function () {
		assertUserNotLoggedIn()
		cy.goToApiLoginPageAndLogin(testUserName, testUserPassword)
		//skipIntro()
		//cy.get("#skipButtonWelcome", {timeout: 10000}).click({force: true})
		cy.log('testuser already has reminders so skipping onboarding ang going to inbox')
		asserUrlContains('onboarding')
		//cy.pause()
		cy.logOutViaSettingsPage(true) // Going directly via cy.visit breaks for some reason
		//cy.pause()
		assertUserNotLoggedIn()
		asserUrlContains('intro')
		assertUserNotLoggedIn()
		assertDatalabRedirectsToRegisterPage()
		assertUserNotLoggedIn()
		goToInbox()
		assertUserNotLoggedIn()
		//cy.pause()
		assertOnLoginPage().debug()
		assertUserNotLoggedIn()
	})
  it('Redirects after login', function(){
	  Cypress.Cookies.debug(true)
	  logout();
	  assertDatalabRedirectsToRegisterPage()
	  //cy.debug()
	  cy.log("Cookies: " +  cy.getAllCookies().toString())
	  cy.getCookie('intended_url').should('exist')
	  cy.contains("Sign in here").click()
	  cy.login()
	  cy.getCookie('intended_url').should('exist')
	  cy.url().should('include', datalabPath)
	  cy.log("Checking retain of auth after login")
	  cy.visit(datalabPath)
	  cy.url().should('include', datalabPath)
	  checkUserApi(true)
	  logout();
  })
  it('Logs in with wrong password', function(){
    cy.goToApiLoginPageAndLogin("wrong user", "wrong password")
    cy.contains('I can\'t find a user with those credentials. Please try again').should('be.visible')
  })
  it('Tries to create account with existing username', function () {
    cy.clearCookies()
    cy.visitApi(registerPath)
    cy.get(selectors.usernameInput).type('mike')
    cy.get(selectors.emailInput).type('m@thinkbynumbers.org')
    cy.get(selectors.pw).type(testUserName)
    cy.get(selectors.pwConfirm).type(testUserPassword)
    cy.get(selectors.registerButton).click({ force: true })
    cy.contains('#error-messages', 'The user login has already been taken.')
      //cy.contains('#error-messages', 'The email has already been taken.')
      //cy.contains('#error-messages', 'must match')
  })
  it('Has error message if wrong email on password reset attempt', function () {
    cy.visitApi(`/auth/password/reset`)
    cy.get('.card-body > form > .form-group > .col-md-6 > #email')
            .type('ivsadfy@thinkbynumbers.org')
    cy.get('.container > .row > .col-md-8 > .card > .card-body').click()
    cy.get('.card-body > form > .form-group > .col-md-6 > .btn').click()
    cy.url().should('contain', 'auth/password/reset')
    cy.get('.col-md-8 > .card > .card-body > form > .form-group:nth-child(2)').click()
    // TODO: uncomment after fixing
    // cy.get('.card > .card-body > form > .form-group:nth-child(2) > .col-md-6')
    //     .contains("We can't find a user with that e-mail address.")
  })
})
