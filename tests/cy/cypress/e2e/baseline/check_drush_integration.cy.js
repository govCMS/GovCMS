describe('Check Drush integration', () => {
    it('Try \'drush --version\'', () => {
        cy.drupalDrushCommand("--version").then((result) => {
            cy.log(result.stdout)
        })
    })

})
