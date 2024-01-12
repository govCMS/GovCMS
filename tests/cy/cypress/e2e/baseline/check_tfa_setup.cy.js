// Test setup TFA module.

describe('Check TFA setup', () => {
  beforeEach(() => {
    cy.drupalLogin()
  })

  it('Setup TFA', () => {
    cy.visit('admin/config/people/tfa')
    //cy.wait(2000)
    cy.visit('/admin/config/system/encryption/profiles')
    //cy.wait(2000)
    cy.visit('admin/config/system/encryption/profiles/add')
    //cy.wait(2000)
    cy.visit('admin/config/system/keys')
    //cy.wait(2000)
    cy.visit('admin/config/system/keys/add')
    cy.get('#edit-label').type('TFA Key')
    //cy.wait(2000)
    cy.get('#edit-key-type').select('encryption')
    //cy.wait(2000)
    cy.get('[data-drupal-selector="edit-key-type-settings-key-size"]').select('256')
    //cy.wait(2000)
    cy.get('[data-drupal-selector="edit-key-provider"]').select('env')
   // cy.wait(2000)
    cy.get('[data-drupal-selector="edit-key-provider-settings-env-variable"]').type('KEY_ENCRYPT_TFA')
    //cy.wait(2000)
    cy.get('[data-drupal-selector="edit-key-provider-settings-base64-encoded"]').check()
    //cy.wait(2000)
    cy.get('#key-add-form').submit()
  })
})
