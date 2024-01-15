// Test setup TFA module.
const testKey = 'testkey123'
const testProfile = 'testprofile123'


describe('Check TFA setup', () => {
    beforeEach(() => {
        cy.drupalLogin()
    })

    it('Create encryption key', () => {
        cy.visit('admin/config/system/keys')
        cy.visit('admin/config/system/keys/add')
        cy.get('#edit-label').type(testKey)
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
        cy.get('.messages-list__item').contains(`The key ${testKey} has been added.`)
    })

    it('Create encryption profile', () => {
        cy.visit('admin/config/system/encryption/profiles/add')
        cy.get('#edit-label').type(testProfile)
        cy.wait(250)
        cy.get('[data-drupal-selector="edit-encryption-method"]').select('Authenticated AES (Real AES)')
        cy.wait(250)
        cy.get('[data-drupal-selector="edit-encryption-key"]').select(testKey)
        cy.wait(250)
        cy.get('#edit-submit').click({ force: true })
        cy.get('.messages-list__item').contains(`Saved the ${testProfile} encryption profile.`)
    })

    it('Set up TFA', () =>{
        cy.execDrush('-y cset tfa.settings enabled 1')
        // Enforce TFA set up for Content Author, Content Approver, and Site Admin roles.
        cy.execDrush('-y cset tfa.settings required_roles.govcms_content_author govcms_content_author')
        cy.execDrush('-y cset tfa.settings required_roles.govcms_content_approver govcms_content_approver')
        cy.execDrush('-y cset tfa.settings required_roles.govcms_site_administrator govcms_site_administrator')
        // Set Encryption profile
        cy.execDrush(`-y cset tfa.settings encryption ${testProfile}`)
    })

    it('Check new user is asked to enable TFA', () => {})


    it('Clean up', () => {
        // Remove created key, which automatically deletes the created profile as well.
        cy.visit(`admin/config/system/keys/manage/${testKey}/delete?destination=/admin/config/system/keys`)
        cy.get('#edit-submit').click()
        cy.get('.messages-list__item').contains(`The key ${testKey} has been deleted.`)
    })

})
