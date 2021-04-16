const role = 'govcms-site-admin'
let user_role = ''
describe('User can create a new user with a role', () => {
  it('Create the user '+role , () => {
    cy.createUser(role).then((x) => {
      cy.log(role+' created')
    })
  })

  it('Log the user in then create a new user with a role', () => {
    let user_role = 'govcms-content-author'
    let password = user_role+'#123'
    cy.userLogin(role).then(() => {
      cy.get('#toolbar-link-entity-user-collection')
        .click({force: true})
      cy.get('.local-actions__item > .button')
        .click({force: true})
      // cy.get('#edit-roles-'+user_role)
      //   .click({force: true})
      cy.get('#edit-mail')
        .type('cypress-tester@test.com', {force: true})
      cy.get('#edit-name')
        .type('@cypresstest-'+user_role, {force: true})
      cy.get('#edit-pass-pass1', {force: true})
        .type(password, {force: true})
      cy.get('#edit-pass-pass2', {force: true})
        .type(password, {force: true})
      cy.get('#edit-submit')
        .click({force: true})
      cy.get('.messages-list__item')
        .contains('Created a new user account')
      cy.get('#toolbar-link-entity-user-collection')
        .click({force: true})
      cy.get('#edit-user-bulk-form-0')
        .click({force: true})
      cy.get('#edit-action')
        .select('Add the Content Author role to the selected user(s)')
      cy.get('#edit-submit')
        .click({force: true})
      cy.get('#edit-user-bulk-form-0')
        .click({force: true})
      cy.get('#edit-action')
        .select('Cancel the selected user account(s)')
      cy.get('#edit-submit')
        .click({force: true})
      cy.get('#edit-user-cancel-method-user-cancel-delete')
        .click({force: true})
      cy.get('#edit-submit')
        .click({force: true})
    })
  })

  it('Delete the user', () => {
    cy.userlogout()
    cy.deleteUser(role).then(() => {
      cy.log('User deleted')
    })
  })
})