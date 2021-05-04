# GovCMS

## About GovCMS

<img src="logo.png" alt="GovCMS logo" align="right" width="220px"/>

GovCMS is the Drupal specific version of the GovCMS distribution.

[GovCMS](https://www.govcms.gov.au) is an open source web content management and hosting service, based on Drupal and developed to help agencies create modern, affordable and responsive websites, whilst making it easier to collaborate and innovate. GovCMS also helps reduce the technology and compliance burden on government agencies. GovCMS is managed by the Australian Government Department of Finance.

GovCMS9 Slack channel: https://govcmschat.slack.com/archives/C01BD9B3V5W

---

## Installation

_For an easy, one-line, dev setup see ['Automated Default Dev Setup' wiki page](https://github.com/GovCMS/GovCMS/wiki/Automated-Default-Dev-Setup)._

GovCMS utilizes [Composer](https://getcomposer.org/) to manage its dependencies. So, before using GovCMS, make sure you have Composer installed on your machine.

For best performance with composer 1.x, we recommend adding [Prestissimo](https://github.com/hirak/prestissimo) to your global Composer before installing GovCMS, as it enables dependencies to load in parallel, significantly reducing the install time.

    composer global require "hirak/prestissimo:^0.3"

### Via Composer Create-Project

Composer will create a new directory called MY_PROJECT containing a docroot directory with a full GovCMS code base therein.

    composer create-project --stability dev --prefer-dist govcms/govcms-project MY_PROJECT

[Composer create-project](https://getcomposer.org/doc/03-cli.md#create-project) is the same as doing a git clone, followed by a composer install.

### Installation from source

    git clone -b 2.x-develop git@github.com:GovCMS/GovCMS.git
    cd GovCMS
    composer install

To develop on or patch against GovCMS, the source files should be downloaded and the project built.

### Other Platforms

Additional platform instructions are available in the wiki (https://github.com/GovCMS/GovCMS/wiki).  If you can contribute more methods, please let us know.

**[Back to top](#govcms)**

## Server Requirements

* Apache, Nginx, Microsoft IIS or any other web server with proper PHP support
* MySQL 5.7.8+/MariaDB 10.3.7+/Percona Server 5.7.8+ or higher with PDO and an InnoDB-compatible primary storage engine
* PostgreSQL 10 or higher with the pg_trgm extension.
* SQLite 3.7.11 or higher
* PHP Version 7.4.* or higher
* [Git](http://git-scm.com/)
* [Composer](https://getcomposer.org/)

**[Back to top](#govcms)**

## Troubleshooting and Contributing

If you're encountering some oddities, [here's a list of resolutions](https://github.com/GovCMS/GovCMS/wiki/Troubleshooting) to some of the problems you may be experiencing.

### Contributing to GovCMS

All contributions to GovCMS are welcome. Issues and pull requests may be submitted against the relevant GovCMS project on github where they will be addressed by the GovCMS team.

### Patching GovCMS

Because GovCMS is a [Drupal distribution](https://www.drupal.org/documentation/build/distributions), modules and configurations are not added directly to the codebase. Rather, they are referenced within the composer.json file.

Any alterations to Drupal core or contributed modules must have an associated [drupal.org](https://www.drupal.org) issue filed against the project in question. Modifications should be made directly to the project in question and patched into GovCMS rather than made directly against GovCMS.

It is a requirement for any patches to GovCMS to pass all automated testing prior to manual review. The automated testing checks for PHP syntax, coding standards, build completion and runs behavioural tests. It is also desirable that additions to the codebase add behat tests to ensure no regressions occur once committed.

To submit a patch, the GovCMS project should be forked and changes applied to a branch on the forked repository. Once all changes are applied, a pull request between GovCMS and the branch of the fork may be created.

**[Back to top](#govcms)**
