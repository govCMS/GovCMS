// --- Commands (Functions) ----------------------------------------------------

// Drupal login.
Cypress.Commands.add("drupalLogin", (user, password) => {
    user = user || Cypress.env('user').super.username
    password = password || Cypress.env('user').super.password

    return cy.request({
        method: 'POST',
        url: '/user/login',
        form: true,
        body: {
            name: user,
            pass: password,
            form_id: 'user_login_form'
        }
    });
});

// Drupal logout.
Cypress.Commands.add('drupalLogout', () => {
    return cy.request('/user/logout');
});

// Drupal drush command.
Cypress.Commands.add("drupalDrushCommand", (command) => {
    var cmd = Cypress.env('drupalDrushCmdLine');

    if (cmd == null) {
        if(Cypress.env('localEnv') === "lando"){
            cmd = 'lando drush %command'
        }else{
            cmd = 'drush %command'
        }
    }

    if (typeof command === 'string') {
        command = [command];
    }

    const execCmd = cmd.replace('%command', command.join(' '));

    return cy.exec(execCmd);
});


// Composer command.
Cypress.Commands.add("composerCommand", (command) => {
    var cmd = Cypress.env('composerCmdLine');

    if (cmd == null) {
        if(Cypress.env('localEnv') === "lando"){
            cmd = 'lando composer %command'
        }else{
            cmd = 'composer %command'
        }
    }

    if (typeof command === 'string') {
        command = [command];
    }

    const execCmd = cmd.replace('%command', command.join(' '));

    return cy.exec(execCmd)
});