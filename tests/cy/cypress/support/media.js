Cypress.Commands.add("mediaCheck", (file_type, extension) => {
  cy.userLogin("govcms-site-admin").then(() => {
    let upload_type = file_type
    let file_ext = extension

    cy.log('User Logged in')
    cy.visit("/media/add/"+upload_type).then(() => {
      cy.intercept('POST', 'media/add/'+upload_type).as('upload')
      cy.log("User has landed on the media/add/" +upload_type+ " page")
      cy.get('#edit-name-0-value').type('A '+upload_type+' file',{force: true})
      cy.get('input[id*="edit-field-media-'+ upload_type +'"]').attachFile('media/'+upload_type+'_test.'+file_ext).then(() => {
        if(file_type === 'image'){
          cy.get('input[id*="edit-field-media-image-0-alt"]')
          .type('alt text', {force: true})
        }
        cy.get('#edit-submit').click().wait('@upload').then(() => {
          cy.intercept('/media/add/'+upload_type)
          cy.get('.messages-list__item .messages__content').contains(upload_type+' file has been created.')
          cy.log(upload_type+' file created')
        })
      })
      cy.visit('admin/content/media').then(()=> {
        cy.get('.views-table.views-view-table tbody tr').each((x) => {
          cy.wrap(x)
            .should('contain', upload_type, { matchCase: false })
            .and('contain', 'A '+upload_type+' file')
            .find('input.form-checkbox')
            .click({force: true})
          }).then(() => {
            cy.get('.form-actions input#edit-submit--2')
            .click({force: true}).then(() => {
              cy.get('input#edit-submit')
              .click({force: true}).then(() => {
                cy.get('.messages-list__item .messages__content').contains('Deleted')
              })
            })
          })
      })
    })
  })
})