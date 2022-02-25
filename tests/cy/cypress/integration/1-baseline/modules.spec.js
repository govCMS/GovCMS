import packages from '../../../../../composer.json'

let modules = Object.entries(packages['require']).filter((str) => {
    return str[1].indexOf('^') === -1
})

describe('Cross check module and library versions', () => {
    modules.forEach((module, i) => {
        it(`${module}`, () => {
            cy.composerCommand('show ' + module[0] + ' | grep versions').its('stdout').should('contain', module[1])
        })
    })
})
