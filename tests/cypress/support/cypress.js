// --- Commands (Functions) ----------------------------------------------------

// Set viewport.
Cypress.Commands.add('setViewport', (size = Cypress.env('viewports_desktop')) => {
    if (Cypress._.isArray(size)) {
        cy.viewport(size[0], size[1])
    } else {
        cy.viewport(size)
    }
})