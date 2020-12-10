describe('Check user page', function () {
    before(() => {
        cy.visit("user")
    })

    it("Check login form should be visible for anonymous user", function () {
        cy.get('.user-login-form').should('be.visible')
    })

    it("Test login", function () {
        cy.login();
    })
})