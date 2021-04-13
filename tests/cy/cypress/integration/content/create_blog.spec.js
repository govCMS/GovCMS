const role1 = 'govcms-content-author'
const role2 = 'govcms-content-approver'
const content_type = 'Blog article'
describe('User can create '+content_type+' content', () => {
  it('Create the user '+role1+ ' and '+role2 , () => {
    cy.createUser(role1).then((x) => {
      cy.log('User created')
    })
    cy.createUser(role2).then((x) => {
      cy.log('User created')
    })
  })

  it('Log the user in then create a '+content_type, () => {
    const content_title = 'A '+ content_type +' title'
    cy.userLogin(role1).then(() => {
      cy.get('#toolbar-link-system-admin_content')
      .click()
      cy.get('.local-actions__item > .button')
      .click()
      cy.get('.node-type-list .admin-item__title').contains(content_type)
      .click()
      cy.get('#edit-title-0-value')
      .type(content_title, {force: true})
      cy.type_ckeditor("edit-body-0-value", "<h2>A heading on a page</h2><p>A small piece of a text to make sure we can add info to the body field</p>");
      cy.get('#edit-submit')
      .click()
      cy.get('.messages').should('contain', content_type+' '+content_title+' has been created.')
    })
  })

  it('Delete the user', () => {
    cy.userlogout()
    cy.deleteUser(role1).then(() => {
      cy.log('User deleted')
    })
    cy.deleteUser(role2).then(() => {
      cy.log('User deleted')
    })
  })
})