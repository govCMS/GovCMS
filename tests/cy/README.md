# govcms-testing-cypress

Cypress end-to-end tests for GovCMS distribution

# Running Cypress locally

## Install Cypress and dependencies.

We assume you already have NPM and Yarn installed locally.

First, navigate to tests/cy

```
cd tests/cy
```

Install using yarn

```
npm init -y
yarn add cypress
yarn install
```

Cypress should be now be installed. To check, run

```
yarn run cypress open
```

which should open Cypress' browser testing app.

## Configure local environment

In order to use these tests, you will have to configure some local env vars. First,
we assume that you already have a local GovCMS instance running (i.e using Docker)
with global superadmin already created. We then need to configure two things:
- local url
- Superadmin details.

*Local URL*

In the 'cypress.config.js' file, change the 'baseUrl' value to your local url.

*Superadmin details*

You must create a new file 'cypress.env.json' and add the following details:

```json
{
    "user" : {
      "super" : {
        "username" : "<your username>",
        "password" : "<your password>"
      }
    }
}
```

You are now set up and ready to run tests!

## Running tests

First, you can run tests through Cypress' app as above with

```
yarn run cypress open
```

Alternatively, tests can be run through the command line with

```
yarn run cypress run
```

for all tests, or for specific test(s)

```
yarn run cypress run --spec cypress/e2e/baseline/create_user_with_role.cy.js
```
or
```
yarn run cypress run --spec cypress/e2e/baseline/*
```

