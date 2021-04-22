const role = 'govcms-site-admin'
let user_role = ''
describe('User can create a new user with a role', () => {
  it('Create the user '+role , () => {
    cy.createUser(role).then((x) => {
      cy.log(role+' created')
    })
  })

  it('Log the user in then create a new user with Content Autor role', () => {
    cy.uiCreateUser('Content Author', role)
  })

  it('Log the user in then create a new user with Content Approver role', () => {
    cy.uiCreateUser('Content Approver', role)
  })

  it('Log the user in then create a new user with Site Administrator role', () => {
    cy.uiCreateUser('Site Administrator', role)
  })

  it('Delete the user', () => {
    cy.userlogout()
    cy.deleteUser(role).then(() => {
      cy.log('User deleted')
    })
  })
})