// Define multiple viewports.
const viewports = Cypress.env('viewports')

// Define test cases.
let runTestCases = (viewport, authenticated = 'anonymous') => {
    // Check for PHP errors.
    it('Check for PHP errors', function () {
        cy.checkSiteErrors()
    })
}

describe('Check super user(UID 1)', () => {
    viewports.forEach((viewport) => {
        context(`Check ${viewport} screen login with correct details`, function () {
            before(() => {
                // Set the viewport.
                cy.setViewport(viewport)
                // Visit the link.
                cy.visit('/')
            })
            beforeEach(() => {
                // Set the viewport.
                cy.setViewport(viewport)
            })
            // Check login and logout.
            it('Login, then logout', function () {
                // Visit the link.
                cy.visit("user")
                // Login the user with password.
                cy.get("#edit-name").type(Cypress.env('user').super.username)
                cy.get("#edit-pass").type(Cypress.env('user').super.password)
                // Submit the form.
                cy.get("#edit-submit").click()
                // Check logged in class.
                cy.get('body').should('have.class', 'user-logged-in')
                // Logout the user.
                cy.visit("/user/logout")
                // Check not logged in class.
                cy.get('body').should('not.have.class', 'user-logged-in')
            })
            // Run test cases.
            runTestCases(viewport)
        })
    })
})