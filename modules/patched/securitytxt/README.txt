                      ━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                       SECURITY.TXT MODULE README

                            Daniel J. R. May
                      ━━━━━━━━━━━━━━━━━━━━━━━━━━━━


Table of Contents
─────────────────

1. Introduction
2. Installation
3. Configuration
.. 1. Permissions
.. 2. Security.txt configuration
.. 3. Security.txt signing
4. Use
5. Further reading





1 Introduction
══════════════

  The Security.txt module provides an implementation of the security.txt
  draft RFC standard. Its purpose is to provide a standardised way to
  document your website’s security contact details and policy. This
  allows users and security researchers to securely disclose security
  vulnerabilities to you.


2 Installation
══════════════

  This module should be installed in the usual way. Read about
  [installing modules].


[installing modules]
<https://www.drupal.org/docs/extending-drupal/installing-modules>


3 Configuration
═══════════════

  Once you have installed this module you will want to perform the
  following configuration.


3.1 Permissions
───────────────

  You control the permissions granted to each role at
  `/admin/people/permissions'. You will almost certainly want to give
  everyone the `View security.txt' permission, i.e. give it to both the
  `Anonymous User' and `Authenticated User' roles.

  You will only want to give the `Administer security.txt' permission to
  very trusted roles.


3.2 Security.txt configuration
──────────────────────────────

  The Security.txt configuration page can be found under `System' on the
  Drupal configuration page. Fill in all the details you want to add to
  your `security.txt' file, then press the `Save configuration' button.
  You should then proceed to the `Sign' tab of the configuration form.


3.3 Security.txt signing
────────────────────────

  You can provide a digital signature for your `security.txt' file by
  following the instructions on the `Sign' tab of the module’s
  configuration page.


4 Use
═════

  Once you have completed the configuration of the Security.txt module
  your security.txt and security.txt.sig files will be available at the
  following standard URLs:

  • /.well-known/security.txt
  • /.well-known/security.txt.sig


5 Further reading
═════════════════

  • Learn more about the [security.txt standard]
  • Read the [draft RFC]


[security.txt standard] <https://securitytxt.org/>

[draft RFC] <https://tools.ietf.org/html/draft-foudil-securitytxt-02>
