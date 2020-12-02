describe("verify the functionality of Honeypot module", function () {
  beforeEach(() => {
    cy.login();
  });

  var honeypot = 0;
  var dblog = 0;
  //enable module
  it("enable modules", function () {
    cy.visit("admin/modules");
    cy.get("body input#edit-modules-honeypot-enable").then((body) => {
      console.log(body.is("[disabled=disabled]")); //true means enabled, false means disabled, displays log in browser- inspect- console
      if (body.is("[disabled=disabled]") == true) {
        honeypot = 0;
      }
      if (body.is("[disabled=disabled]") == false) {
        cy.get("form")
          .find("#edit-modules-honeypot-enable")
          .check({ force: true });
        //save the change
        cy.get("#edit-submit").click();
        //confirm the change
        cy.get("#edit-submit").click();
        honeypot = 1;
      }
    });
    cy.get("body input#edit-modules-dblog-enable").then((body) => {
      console.log(body.is("[disabled=disabled]")); //true means enabled, false means disabled, displays log in browser- inspect- console
      if (body.is("[disabled=disabled]") == true) {
        dblog = 0;
      }
      if (body.is("[disabled=disabled]") == false) {
        cy.get("form")
          .find("#edit-modules-dblog-enable")
          .check({ force: true });
        //save the change
        cy.get("#edit-submit").click();
        //confirm the change
        cy.get("#edit-submit").click();
        dblog = 1;
      }
    });
    //    cy.contains("Module Honeypot has been enabled.")
  });

  it("configure Honypot configuration", function () {
    cy.visit("/admin/config/content/honeypot");

    cy.get("#edit-log").check({ force: true });

    cy.get("#edit-element-name").clear({ force: true }).type("myaddress");

    cy.get("#edit-form-settings-user-pass").check({ force: true });
    cy.contains("Save configuration").click({ force: true });

    cy.contains("The configuration options have been saved.");
  });

  it("Create a dummy user", function () {
    cy.visit("/admin/people/create");
    cy.get("#edit-mail").type("honeypot.test@email.a", { force: true });
    cy.get("#edit-name").type("honeypot.test@email.a", { force: true });
    cy.get("#edit-pass-pass1").type("Password123", { force: true });
    cy.get("#edit-pass-pass2").type("Password123", { force: true });
    cy.get("#edit-submit").click({ force: true });
  });

  it("Correct password reset form", function () {
    cy.visit("/user/logout");

    cy.visit("/user/password");
    //wait 5 seconds otherwise considered as a bot
    //I have commented out the wait command as it is considered as problem when committing this script to github
    cy.wait(5000);
    cy.get("#edit-name").type("honeypot.test@email.a");
    cy.wait(5000);
    cy.get("#edit-submit").click();

    cy.contains("Further instructions have been sent to your email address.");
  });

  it("Tiggers honeypot", function () {
    cy.visit("/user/logout");

    cy.visit("/user/password");
    //wait 5 seconds otherwise considered as a bot
    //I have commented out the wait command as it is considered as problem when committing this script to github
    //cy.wait(8000)

    cy.get("#edit-name").type("honeypot.test@email.a");

    cy.get("#edit-myaddress").type("blockme", { force: true });

    cy.get("#edit-submit").click();

    cy.contains("There was a problem with your form submission.");
  });

  it("check logs", function () {
    cy.visit("/admin/reports/dblog");
    cy.get(".views-field-type").contains("honeypot");
    cy.get(".views-field-message > a").contains(
      "Blocked submission of user_pass due"
    );
  });

  it("Remove the dummy user", function () {
    cy.visit("/admin/people");
    cy.get(
      ":nth-child(1) > .views-field-operations > .dropbutton-wrapper > .dropbutton-widget > .dropbutton > .edit > a"
    ).click({ Force: true });
    cy.get("#edit-delete").click({ force: true });
    cy.get("#edit-user-cancel-method-user-cancel-delete").click({
      force: true,
    });
    cy.get("#edit-submit").click({ force: true });
    cy.wait(5000);
  });

  it("disable module if", function () {
    cy.visit("/admin/modules/uninstall");
    cy.get("form").then((body) => {
      console.log(dblog);
      if ((dblog = 0)) {
        cy.visit("/admin/modules/uninstall");
      }
      if ((dblog = 1)) {
        cy.get("form").find("#edit-uninstall-dblog").check({ force: true });
        //save the change
        cy.get("#edit-submit").click();
        //confirm the change
        cy.get("#edit-submit").click();
      }
    });
    //honeypot will be still enabled as it is default
  });
});
