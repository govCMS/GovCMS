// *********************************************************
// As a GovCMS site administrator, I should be able to create new
// users with either a 'Content Author', 'Content Approver', or
// 'Site Administrator' role.
const testRole = 'govcms-site-admin'

describe('User can create a new user with a role', () => {
  const roles = ['Content Author', 'Content Approver', 'Site Administrator']
  for (const userRole of roles) {
    it('Create a new user with ' + userRole + ' role', () => {
      cy.checkUserCreation(userRole, testRole)
    })
  }
  it('Clean up and log out', () => {
    cy.drupalLogout()
  })
})


// ***********************************************
// userrole is the plain english version of the role ie.
// govcms-content-author would be Content Author
// role is the user that should login to Drupal
Cypress.Commands.add('checkUserCreation', (userrole, testRole) => {
    let user_role_machine_name = 'govcms-' + userrole.toLowerCase().replace(' ', '-')
    let password = user_role_machine_name + '#123'
    cy.userLogin(testRole).then(() => {
        cy.get('#toolbar-link-entity-user-collection')
            .click({ force: true })
        cy.get('.local-actions__item > .button')
            .click({ force: true })
        cy.get('#edit-mail')
            .type('cypress-tester-' + user_role_machine_name + '@test.com', { force: true })
        cy.get('#edit-name')
            .type('@cypresstest-' + user_role_machine_name, { force: true })
        cy.get('#edit-pass-pass1', { force: true })
            .type(password, { force: true })
        cy.get('#edit-pass-pass2', { force: true })
            .type(password, { force: true })
        cy.get('#edit-submit')
            .click({ force: true })
        cy.get('.messages-list__item')
            .contains('Created a new user account')
        cy.get('#toolbar-link-entity-user-collection')
            .click({ force: true })
        cy.get('#edit-user-bulk-form-0')
            .click({ force: true })
        cy.get('#edit-action')
            .select('Add the ' + userrole + ' role to the selected user(s)')
        cy.get('#edit-submit')
            .click({ force: true })
        cy.get('#edit-user-bulk-form-0')
            .click({ force: true })
        cy.get('#edit-action')
            .select('Cancel the selected user account(s)')
        cy.get('#edit-submit')
            .click({ force: true })
        cy.get('#edit-user-cancel-method-user-cancel-delete')
            .click({ force: true })
        cy.get('#edit-submit')
            .click({ force: true })
    })
})
