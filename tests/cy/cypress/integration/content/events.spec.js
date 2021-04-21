// Define multiple viewports.
const viewports = Cypress.env('viewports')

// Define test cases.
let runTestCases = (viewport, authenticated = 'anonymous') => {
    // Check for PHP errors.
    it('Check for PHP errors', function () {
        cy.checkSiteErrors()
    })
}

describe('Check Events landing page', () => {
    viewports.forEach((viewport) => {
        context(`Check ${viewport} screen login with correct details`, function () {
            before(() => {
                // Set the viewport.
                cy.setViewport(viewport)
                // Visit the link.
                cy.visit('events')
            })
            beforeEach(() => {
                // Set the viewport.
                cy.setViewport(viewport)
            })
            // Run test cases.
            runTestCases(viewport)
        })
    })
})