// load type definitions that come with Cypress module
function createNewApplication(){
    cy.get('#create-app-button', {timeout: 10000}).click({force: true})
    // TODO: Uncomment this. For some reason it's incorrectly failing intermittently now.
    // cy.checkForBrokenImages()
    const d = new Date()
    let testAppName = `test-app${d.getTime()}`
    cy.get('#app_display_name').type(testAppName, {force: true})
    cy.get('#app_display_name').type(testAppName, {force: true})
    cy.get('#client_id').type(testAppName, {force: true})
    cy.get('#app_description').type(testAppName, {force: true})
    cy.get('#homepage_url').type("https://" + testAppName + ".com", {force: true})
    cy.get('.btn-success').click({force: true})
    cy.log('Need to start redirecting to builder.quantimo.do...')
    cy.url().should('contain', '/edit', {timeout: 120000})
    // cy.checkForBrokenImages() checkForBrokenImages is randomly slow here for some reason
    // cy.get('iframe#iframe md-tabs-canvas.md-paginated > md-pagination-wrapper >
    // md-tab-item.md-tab.md-ink-ripple:nth-of-type(2) > span',
    //     {timeout: 120000})
    //     .click({ force: true });
    // cy.get('iframe#iframe .md-bar').click({ force: true });
}
/// <reference types="cypress" />
describe('Applications', function(){
    it('Creates a client app as new and existing user', function(){
        cy.visitApi(`/api/v2/apps#`)
	    let d = new Date()
	    let username = `testuser${d.getTime()}`
	    let email = `testuser${d.getTime()}@gmail.com`
	    let password = 'testing123'
        cy.enterNewUserCredentials(username, email, password)
        createNewApplication()
	    cy.get('#dropdown-user-menu-logout-button').click({force: true})
	    cy.url().should('contain', '/intro')
        cy.goToApiLoginPageAndLogin(email, password)
	    cy.visitApi(`/api/v2/apps#`)
        createNewApplication()
    })
})
