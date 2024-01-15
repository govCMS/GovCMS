// ****************************************************************************
// As a GovCMS site admin I should be able to create new views
// ****************************************************************************
describe('Site admin can create a view', () => {

    it('Log the user in then create a new view', () => {
        cy.drupalLogin()
        cy.visit('/admin/structure/views')
        cy.get('.button')
            .click({ force: true })
        cy.get('#edit-label')
            .type('[@cypresstest]-view{enter}', { force: true })
        cy.get('#edit-page-create')
            .click({ force: true })
        cy.get('#edit-submit')
            .click({ force: true })
        cy.get('#edit-actions-submit')
            .click({ force: true })
        cy.get('.messages-list__item')
            .contains('The view [@cypresstest]-view has been saved')
        cy.visit('/-cypresstest--view')
        cy.visit('/admin/structure/views/view/_cypresstest_view/delete?destination=/admin/structure/views')
        cy.get('#edit-submit')
            .click({ force: true })
    })

})
