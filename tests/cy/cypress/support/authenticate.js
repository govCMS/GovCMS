// --- Commands (Functions) ----------------------------------------------------

// Drupal login.
Cypress.Commands.add("drupalLogin", (user, password) => {
  user = user || Cypress.env('user').super.username
  password = password || Cypress.env('user').super.password

  cy.visit(`/user/login`)

  cy.get("#edit-name").type(user)
  cy.get("#edit-pass").type(password)
  cy.get("#edit-submit").click()
});

// Drupal logout.
Cypress.Commands.add('drupalLogout', () => {
  return cy.request('/user/logout');
});
