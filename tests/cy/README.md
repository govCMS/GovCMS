# govcms-testing-cypress

Cypress end-to-end tests for GovCMS distribution

## Using the CircleCI Local CLI

### Requirements
* [Docker](https://docs.docker.com/install/)
* [CircleCI CLI](https://circleci.com/docs/2.0/local-cli/)
* [Homebrew](https://brew.sh/) (Optional but recommended, for MacOS)

### Run examples

#### Run Cypress tests
```
circleci local execute --job test-cypress
```

## Alternative, Docker setup

### Requirements
* [Docker](https://docs.docker.com/install/)
* [Homebrew](https://brew.sh/) (Optional but recommended, for MacOS)

### Run examples

#### Full test with a single Docker command
You can run through _all_ Cypress tests using the following shell command:
```
docker run --ipc=host --cpus=2 -it -v $PWD:/e2e -w /e2e -e false cypress/included:<CYPRESS_VERSION>
```

#### Run a single spec
```
docker run --ipc=host --cpus=2 -it -v $PWD:/e2e -w /e2e -e false cypress/included:<CYPRESS_VERSION> run --spec cypress/integration/search/search.spec.js
```

#### Explanation of the "docker run" command line arguments
```
 --ipc=host       = To prevent Electron Chromium error, see: https://github.com/cypress-io/cypress/issues/350
 --cpus=2         = To prevent Electron Chromium error
 -it              = interactive terminal
 -v $PWD:/e2e     = map current folder to /e2e inside the container
 -w /e2e          = set working directly to /e2e
<CYPRESS_VERSION> = Cypress version number - Optional, if omitted: will use the latest version
 ```

Read more: https://www.cypress.io/blog/2019/05/02/run-cypress-with-a-single-docker-command/

## Alternative, non-Docker method: Yarn setup

### Requirements
* [yarn](https://yarnpkg.com/en/)
* [Homebrew](https://brew.sh/) (Optional but recommended, for MacOS)

You can install Yarn through the Homebrew package manager. This will also install Node.js if it is not already installed.

```
brew install yarn
```

Install Dependencies

```
cd PROJECT
yarn install
```

### Run examples

#### Run all tests
```
yarn run cypress run
```

#### Run a single spec
```
yarn run cypress run --spec cypress/integration/search/search.spec.js
```

## Features

### Drupal Collection

#### drupalLogin( username , password )
Begins the user's authenticated session.
```JavaScript
cy.drupalLogin('admin', 'admin')
```

#### drupalLogout()
Ends the user's authenticated session.
```JavaScript
cy.drupalLogout()
```

#### drupalDrushCommand( command )
Issues a drush command.

The command can be passed as string or an array.
```JavaScript
cy.drupalDrushCommand('status');

cy.drupalDrushCommand(['upwd', 'admin', 'admin']);
```

### `xpath` command

Ref: https://github.com/cypress-io/cypress-xpath/blob/master/README.md

```js
it('finds list items', () => {
  cy.xpath('//ul[@class="todo-list"]//li')
    .should('have.length', 3)
})
```

You can also chain `xpath` off of another command.

```js
it('finds list items', () => {
  cy.xpath('//ul[@class="todo-list"]')
    .xpath('./li')
    .should('have.length', 3)
})
```

As with other cy commands, it is scoped by `cy.within()`.

```js
it('finds list items', () => {
  cy.xpath('//ul[@class="todo-list"]').within(() => {
    cy.xpath('./li')
      .should('have.length', 3)
  });
})
```