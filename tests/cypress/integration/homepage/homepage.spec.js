it("check for header",function (){
  cy.visit("/")
  cy.get('.region-header')
})
it("Check for menu", function() {
  cy.visit("/")
  cy.get('#block-govcms-bartik-main-navigation > .content > .clearfix')
})
it("Check for title", function(){
  cy.visit("/")
  cy.get('.node__title')
})
it("Check for sidenav", function(){
  cy.visit("/")
  cy.get('#block-govcms-bartik-sidebar-navigation')
})
it("Check for content", function(){
  cy.visit("/")
  cy.get('.node__content')
  cy.get('#content')
})
it("Check for footer", function(){
  cy.visit("/")
  cy.get('.site-footer__top')
})
