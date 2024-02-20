# GovCMS

GovCMS is an open-source Drupal distribution developed specifically for Australian government agencies. It is built on
top of the Drupal content management system, providing a range of features and functionalities that are tailored to the
unique needs of government websites.

## Features

Key features of GovCMS include:

- Accessibility compliance: GovCMS is designed to comply with the Web Content Accessibility Guidelines (WCAG) 2.1 Level
  AA, making it easier for government agencies to ensure that their websites are accessible to all users.
- Content moderation: GovCMS includes a range of content moderation workflows, making it easy for government agencies to
  manage content and ensure that only approved content is published on their websites.
- Security: GovCMS is built with security in mind, with regular security updates and patches provided by the Drupal and
  GovCMS Ops team.

## Community

GovCMS Slack channel:

https://govcmschat.slack.com/archives/C01BD9B3V5W

## Getting started

To get started with GovCMS, you need to have the following prerequisites:

- A web server like Apache or Nginx
- PHP version 8.1 or above
- MySQL or PostgresSQL database

More documents can be found in:

- [DEVELOPMENT.md](DEVELOPMENT.md)
- [SECURITY.md](SECURITY.md)
- [VERSIONS.md](VERSIONS.md)

## Troubleshooting and Contributing

If you're encountering some
oddities, [here's a list of resolutions](https://github.com/GovCMS/GovCMS/wiki/Troubleshooting) to some of the problems
you may be experiencing.

### Contributing to GovCMS

All contributions to GovCMS are welcome. Issues and pull requests may be submitted against the relevant GovCMS project
on github where they will be addressed by the GovCMS team.

### Patching GovCMS

Because GovCMS is a [Drupal distribution](https://www.drupal.org/documentation/build/distributions), modules and
configurations are not added directly to the codebase. Rather, they are referenced within the composer.json file.

Any alterations to Drupal core or contributed modules must have an associated [drupal.org](https://www.drupal.org) issue
filed against the project in question. Modifications should be made directly to the project in question and patched into
GovCMS rather than made directly against GovCMS.

It is a requirement for any patches to GovCMS to pass all automated testing prior to manual review. The automated
testing checks for PHP syntax, coding standards, build completion and runs behavioural tests. It is also desirable that
additions to the codebase add behat tests to ensure no regressions occur once committed.

To submit a patch, the GovCMS project should be forked and changes applied to a branch on the forked repository. Once
all changes are applied, a pull request between GovCMS and the branch of the fork may be created.

## License

GovCMS is released under the GNU General Public License v2.0. See the LICENSE file in the root of the repository for
more information.

**[Back to top](#govcms)**
