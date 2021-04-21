const role = 'govcms-site-admin'
describe('User can create a new view', () => {
  it('Create the user '+role , () => {
    cy.createUser(role).then((x) => {
      cy.log(role+' created')
    })
  })

  it('Log the user in then create a new view', () => {
    cy.userLogin(role).then(() => {
      cy.visit('/admin/structure/views')
      cy.get('.button')
        .click({force: true})
      cy.get('#edit-label')
        .type('[@cypresstest]-view{enter}', {force: true})
      cy.get('#edit-page-create')
        .click({force: true})
      cy.get('#edit-submit')
        .click({force: true})
      cy.get('#edit-actions-submit')
        .click({force: true})
      cy.get('.messages-list__item')
        .contains('The view [@cypresstest]-view has been saved')
      cy.visit('/-cypresstest--view')
      cy.visit('/admin/structure/views/view/_cypresstest_view/delete?destination=/admin/structure/views')
      cy.get('#edit-submit')
        .click({force: true})
    })
  })

  it('Delete the user ' + role, () => {
    cy.userlogout()
    cy.deleteUser(role).then(() => {
      cy.log('User deleted')
    })
  })
})