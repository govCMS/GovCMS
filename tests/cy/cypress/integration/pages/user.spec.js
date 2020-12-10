describe('Check user page', function () {
    before(() => {
        cy.visit("user")
    })

    it("Check user login form should be visible for anonymous user", function () {
        cy.get('.user-login-form').should('be.visible')
    })

    it("Test user login", function () {
        cy.login();
    })
})