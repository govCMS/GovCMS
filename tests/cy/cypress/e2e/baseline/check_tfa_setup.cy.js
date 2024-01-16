// Test setup TFA module.
const testKey = 'testkey123'
const testProfile = 'testprofile123'
const testUsername = 'avril'


describe('Check TFA setup', () => {
    // beforeEach(() => {
    //     cy.drupalLogin()
    // })

    it('Create encryption key', () => {
        cy.drupalLogin()
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
        cy.drupalLogin()
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
        cy.execDrush('-y cset tfa.settings validation_skip 10')
        // Enforce TFA set up for Content Author, Content Approver, and Site Admin roles.
        cy.execDrush('-y cset tfa.settings required_roles.govcms_content_author govcms_content_author')
        cy.execDrush('-y cset tfa.settings required_roles.govcms_content_approver govcms_content_approver')
        cy.execDrush('-y cset tfa.settings required_roles.govcms_site_administrator govcms_site_administrator')
        // Set Encryption profile
        cy.execDrush(`-y cset tfa.settings encryption ${testProfile}`)
    })

    it('Check new user is asked to enable TFA', () => {
        cy.visit('user/logout')
        cy.execDrush(`user:create ${testUsername} --password=password`)
        cy.execDrush(`user:role:add govcms_content_author ${testUsername}`)
        cy.execDrush('role:perm:add govcms_content_author \'setup own tfa\'')
        // Log in as the new user.
        cy.visit('user')
        cy.get("#edit-name").type(`${testUsername}`)
        cy.get("#edit-pass").type('password')
        cy.get("#edit-submit").click()
        // Check user is prompted to set up TFA.
        cy.get('.messages.messages--error').contains(`You are required to setup two-factor authentication.`)
    })

    it('Check user can set up TFA', () => {
        let SECRET_KEY;
        // Login
        cy.visit('user/logout')
        cy.visit('user')
        cy.get("#edit-name").type(`${testUsername}`)
        cy.get("#edit-pass").type('password')
        cy.get("#edit-submit").click()
        // Set up TFA
        cy.get('.messages.messages--error').within(() => {
            cy.get('a').click()
        })
        cy.get('[data-drupal-selector="edit-link"]').within(() => {
            cy.get('a').click()
        })
        cy.get('[data-drupal-selector="edit-current-pass"]').type('password')
        cy.get('#edit-submit').click()
        cy.get('[data-drupal-selector="edit-seed"]').invoke('val').then((val) => {
            SECRET_KEY = val
            cy.log(SECRET_KEY)
            cy.task("generateOTP", SECRET_KEY).then(token => {
                cy.get('[data-drupal-selector="edit-code"]').type(token)
            })
        })
        cy.get('[data-drupal-selector="edit-login"]').click()

    })

    it('Clean up', () => {
        // Disable TFA.
        cy.execDrush('-y cset tfa.settings enabled 0')
        cy.drupalLogin()
        // Remove created key, which automatically deletes the created profile as well.
        cy.visit(`admin/config/system/keys/manage/${testKey}/delete?destination=/admin/config/system/keys`)
        cy.get('#edit-submit').click()
        cy.get('.messages-list__item').contains(`The key ${testKey} has been deleted.`)
        // Remove user created for testing purposes
        cy.execDrush(`-y user:cancel --delete-content ${testUsername}`)
    })

})
