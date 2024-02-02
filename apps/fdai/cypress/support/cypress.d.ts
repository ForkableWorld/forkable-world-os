// in cypress/support/index.d.ts
// load type definitions that come with Cypress module
/// <reference types="cypress" />
declare namespace Cypress {
    // noinspection JSUnusedGlobalSymbols
    interface Chainable {
        allowUncaughtException(expectedErrorMessage?: string): Chainable
        assertInputValueContains(selector: string, expected: string): Chainable
        assertInputValueDoesNotContain(selector: string, expected: string): Chainable
        assertInputValueEquals(selector: string, expected: string): Chainable
        checkForBrokenImages(): Chainable
        clearAndType(selector: string, text: string): Chainable
        clickActionSheetButtonContaining(str?: string): Chainable
        containsCaseInsensitive(selector: string, content: string): Chainable
        disableSpeechAndSkipIntro(): Chainable
        login(usernameSelector?: string, username?: string, passwordSelector?: string, password?: string, submitSelector?: string ): Chainable
		loginIonic(usernameSelector?: string, username?: string, passwordSelector?: string, password?: string, submitSelector?: string ): Chainable
		enterNewUserCredentials(username: string, email: string, password: string): Chainable<Element>
        getInDocument(document: any, selector: any): Chainable
        getWithinIframe(targetElement: any): Chainable
        goToApiLoginPageAndLogin(email?: string, password?: string): Chainable
		getTestuserFromAPI(): Chainable
        goToMobileConnectPage(): Chainable
        iframeLoaded($iframe: any): Chainable
        loginWithAccessTokenIfNecessary(path: string, waitForAvatar?: boolean): Chainable
        logoutViaApiLogoutUrl(): Chainable
        logOutViaSettingsPage(useMenuButton: boolean): Chainable
        searchAndClickTopResult(variableName: string, topResultShouldContainSearchTerm?: boolean): Chainable
        setTimeZone(): Chainable
        sendSlackNotification(messageBody?: any): Chainable
        visitApi(url: string, options?: Partial<VisitOptions>): Chainable
        visitWithApiUrlParam(url: string, options?: Partial<VisitOptions>): Chainable
        visitIonicAndSetApiUrl(path: string): Chainable
        toastContains(str: string): Chainable
        getOAuthAppUrl(): string
        oauthAppIsHTTPS(): boolean
        clickLink(label: string) // cy.clickLink('Buy Now')
    }
}
