describe('Check Drush integration', () => {
    it('Try \'drush --version\'', () => {
        cy.execDrush("--version").then((result) => {
            cy.log(result.stdout)
        })
    })

    it('Try enabling TFA', ()=>{
        cy.execDrush("-y cset tfa.settings enabled 1").then((result) => {
            cy.log(result.stdout)
            cy.log(result.stderr)
        })
    })

})
