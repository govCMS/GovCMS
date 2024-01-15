// Define multiple viewports.
const viewports = Cypress.env('viewports')

// Define test cases.
let runTestCases = (viewport, authenticated = 'anonymous') => {
  // Check for PHP errors.
  it('Check for PHP errors', function () {
    cy.checkSiteErrors()
  })
}

const pagesToTest = ['/', 'news-and-media', 'freedom-of-information', 'events', 'blog']

for (const page of pagesToTest) {
  describe(`Check ${page} page`, () => {
    viewports.forEach((viewport) => {
      context(`Check ${viewport} screen login with correct details`, function () {
        before(() => {
          // Set the viewport.
          cy.setViewport(viewport)
          // Visit the link.
          cy.visit(page)
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
}

