import packages from '../../../../../composer.json'

let modules = Object.entries(packages['require']).filter((str) => {
    return str[0].indexOf('drupal/') !== -1
        && str[1].indexOf('^') === -1
        && str[0].indexOf('drupal/core-recommended') === -1
        && str[0].indexOf('theme') === -1
        && str[0].indexOf('update_notifications_disable') === -1
})

describe('Cross check module versions', () => {
    modules.forEach((module, i) => {
        it(`${module}`, () => {
            cy.composerCommand('show ' + module[0] + ' | grep versions').its('stdout').should('contain', module[1])
        })
    })
})
