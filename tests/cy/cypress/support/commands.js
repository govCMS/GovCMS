import 'cypress-file-upload';

// ***********************************************
// The Cypress recommendation is for selectors to use a variant of the following: <div data-cy="box"></div> to be used in tetst as cy.get('[data-cy=box]')
// For the most part you will be fetching data-* constantly so aliasing these elements is useful this done by: cy.get('[data-cy=box]').as('box') and then you can use cy.get('@box')
// The below command does the all the aliasing for you.
// @example beforeEach(() => cy.aliasAll())
Cypress.Commands.add("aliasAll", () =>
    cy.get("[data-cy]").then((list) => {
        list.each((i, {dataset: {cy: name}}) => {
            if (name) {
                cy.get(`[data-cy="${name}"]`).as(name)
            }
        })
    })
)

// Drupal drush command.
Cypress.Commands.add("execDrush", (command) => {
    const cmd = Cypress.env('drupalDrushCmdLine');
    if (typeof command !== 'string') {
        throw Error("Drush command must be a string")
    }
    command = [command];
    const execCmd = cmd.replace('%command', command.join(' '));
    return cy.exec(execCmd);
});


// Composer command.
Cypress.Commands.add("composerCommand", (command) => {
    var cmd = Cypress.env('composerCmdLine');

    if (cmd == null) {
        console.log(Cypress.env())
        if (Cypress.env('localEnv') === "lando") {
            cmd = 'cd ../..; lando composer %command'
        } else {
            cmd = 'cd ../..; composer %command'
        }
    }

    if (typeof command === 'string') {
        command = [command];
    }

    const execCmd = cmd.replace('%command', command.join(' '));

    return cy.exec(execCmd)
});

Cypress.Commands.add("createUser", (siteRole) => {
    cy.govcmsInitialLogin()
    cy.fixture(`users/${siteRole}.json`).then((user) => {
        const username = user.firstname + user.lastname
        const password = user.password
        const email = user.email
        cy.visit('admin/people/create')
        cy.get('#edit-mail')
            .type(email, {force: true})
        cy.get('#edit-name')
            .type(username, {force: true})
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
            .select('Add the ' + siteRole + ' role to the selected user(s)')
        cy.get('#edit-submit')
            .click({force: true})
    })
})

Cypress.Commands.add("deleteUser", (siteRole) => {
    cy.fixture(`users/${siteRole}.json`).then((user) => {
        const username = user.firstname + user.lastname
        cy.drupalDrushCommand(["ucan", "--delete-content", username, "-y"])
    })
})

Cypress.Commands.add("userLogin", (siteRole) => {
    if (siteRole === 'govcms-site-admin') {
        cy.drupalLogin()
    } else {
        cy.fixture(`users/${siteRole}.json`).then((user) => {
            const username = user.firstname + user.lastname
            const password = user.password
            cy.visit(`/user/login`)
            //cy.aliasAll()
            cy.get("#edit-name").type(username)
            cy.get("#edit-pass").type(password)
            cy.get("#edit-submit").click()
        })
    }
})


Cypress.Commands.add("type_ckeditor", (element, content) => {
    cy.window()
        .then(win => {
            win.CKEDITOR.instances[element].setData(content);
        });
})


