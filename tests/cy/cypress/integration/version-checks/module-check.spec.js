describe("Module version check", () => {
  const { softAssert, softExpect } = chai
  // go and grab the composer file from the base repo and store it locally
  before(()=>{
    cy.log(Cypress.env('url'))
    cy.fixture('locations').then((location) => {
      const github = location.github
      cy.request(github).then((response) => {
        cy.writeFile('cypress/fixtures/currentComposer.json' , response.body)
      })
    })
    
  })
  
  it("Get module names and verions", () => {
    cy.fixture('currentComposer').as('x')
    cy.get('@x').then((response) => {
      const moduleNames = []
      cy.get(response.require).each((items) => {
        Object.entries(items).map((key) => {
          cy.get(key).then((data) => {
            let name = data[0]
            let version = data[1]
            cy.log('The module is ' + name + ' and its version is ' + version)
            if(name.includes("drupal")){
              cy.composerCommand(['show', name + '|grep versions']).then((result) => {
                result = result.stdout.toString()
                softExpect(result).to.include(version)
              })
              let machineName = name.replace('drupal/', '')
              moduleNames.push(machineName)
            }
          })
        })
      })
    })
  })
})


