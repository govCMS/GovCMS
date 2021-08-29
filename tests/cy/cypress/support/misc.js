// --- Commands (Functions) ----------------------------------------------------

// Command to check images are loading properly.
Cypress.Commands.add('checkImagesLoading', { prevSubject: 'element' }, (subject) => {
    cy.get(subject.selector).as('container')

    cy.get('@container').find('img').each(($img) => {
        cy.request($img.attr('src')).then((response) => {
            expect(response.status).to.eq(200)
        })
    })
});

// Command to check for PHP errors/warnings/notices.
Cypress.Commands.add('checkSiteErrors', () => {
    // MAMP error output.
    cy.get('.xdebug-error').should('not.exist')
    // Standard PHP error output.
    cy.contains('b', /Error|Warning|Notice|Deprecated/).should('not.exist')
    // Drupal message
    cy.get('h2#message-error-title,h2#message-warning-title').should('not.exist')
});