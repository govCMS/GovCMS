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
