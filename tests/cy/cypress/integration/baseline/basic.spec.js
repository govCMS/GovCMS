const role = 'govcms-site-admin'
let user_role = ''

describe('Basic tests to ensure setup is correct', () => {
  it('Check initial page', ()=>{
    cy.visit('')
    cy.screenshot()
    cy.exec('ahoy cli; bin/drush --version').then((result) => {
      cy.log(result.stdout);
    })



  })

})
