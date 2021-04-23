describe('Verify user able to create a new user', () => {
  it('Create the user', () => {
    cy.createUser("govcms-site-admin").then((x) => {
      cy.log(x)
      cy.log('User created')
    })
  })

  it('Login in the user and create and delete a audio file', () => {
    cy.mediaCheck('audio', 'mp3')
  })

  it('Login in the user and create and delete a video file', () => {
    cy.mediaCheck('video', 'mp4')
  })

  it('Login in the user and create and delete a pdf file', () => {
    cy.mediaCheck('document', 'pdf')
  })

  it('Login in the user and create and delete a word file', () => {
    cy.mediaCheck('document', 'docx')
  })

  it('Login in the user and create and delete a image file', () => {
    cy.mediaCheck('image', 'jpeg')
  })

  it('Delete the user', () => {
    cy.userlogout()
    cy.deleteUser("govcms-site-admin").then(() => {
      cy.log('User deleted')
    })
  })
})