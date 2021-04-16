import 'cypress-file-upload';

// ***********************************************
// The Cypress recommendation is for selectors to use a variant of the following: <div data-cy="box"></div> to be used in tetst as cy.get('[data-cy=box]')
// For the most part you will be fetching data-* constantly so aliasing these elements is useful this done by: cy.get('[data-cy=box]').as('box') and then you can use cy.get('@box')
// The below command does the all the aliasing for you.
// @example beforeEach(() => cy.aliasAll())
Cypress.Commands.add("aliasAll", () =>
  cy.get("[data-cy]").then((list) => {
    list.each((i, { dataset: { cy: name } }) => {
      if (name) {
        cy.get(`[data-cy="${name}"]`).as(name)
      }
    })
  })
)

Cypress.Commands.add("createUser", (siteRole) => {
  cy.fixture(`users/${siteRole}.json`).then((user) => {
    const username = user.firstname + user.lastname
    const password = user.password
    const email = user.email
    const role = user.role
    cy.drupalDrushCommand([
      "ucrt",
      username,
      `--mail="${email}"`,
      `--password="${password}"`,
    ]).then(() => {
      cy.drupalDrushCommand(["urol", role, username])
    })
  })
})

Cypress.Commands.add("deleteUser", (siteRole) => {
  cy.fixture(`users/${siteRole}.json`).then((user) => {
    const username = user.firstname + user.lastname
    cy.drupalDrushCommand(["ucan", "--delete-content", username, "-y"])
  })
})

Cypress.Commands.add("userLogin", (siteRole) => {
  cy.fixture(`users/${siteRole}.json`).then((user) => {
    const username = user.firstname + user.lastname
    const password = user.password
    cy.visit(`/user/login`)
    //cy.aliasAll()
    cy.get("#edit-name").type(username)
    cy.get("#edit-pass").type(password)
    cy.get("#edit-submit").click()
  })
})

Cypress.Commands.add("userlogout", () => {
  cy.visit(`/user/logout`)
})