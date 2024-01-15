// Test setup TFA module.

describe('Check TFA setup', () => {
  beforeEach(() => {
    cy.drupalLogin()
  })

  it('Create encryption key', () => {
    cy.visit('admin/config/system/keys')
    cy.visit('admin/config/system/keys/add')
    cy.get('#edit-label').type("test2")
    // For some reason the site needs time here otherwise an error is thrown.
    // Something to do with the verification of #edit-label being machine-readable.
    cy.wait(500)
    cy.get('#edit-key-type').select('encryption')
    cy.get('[data-drupal-selector="edit-key-type-settings-key-size"]').select('256')
    cy.get('[data-drupal-selector="edit-key-provider"]').select('config')
    cy.get('[data-drupal-selector="edit-key-provider-settings-base64-encoded"]').check()
    cy.get('[data-drupal-selector="edit-key-input-settings-key-value"]').type(Cypress.env("encryption_profile_key"))
    cy.get('[data-drupal-selector="edit-key-input-settings-base64-encoded"]').check()
    cy.get('#key-add-form').submit()
  })




  it('Clean up', () => {
    cy.visit('admin/config/system/keys')






  })


})
