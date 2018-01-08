# govCMS8
<img src="https://www.drupal.org/files/styles/grid-3/public/project-images/govcms8.png" alt="govCMS8 logo" align="right"/>

[![Build Status](https://travis-ci.org/govCMS/govCMS8.svg?branch=8.4.x)](https://travis-ci.org/govCMS/govCMS8)

govCMS8 is the Drupal 8-specific version of the govCMS distribution.

[govCMS](https://www.govcms.gov.au) is an open source web content management and hosting service, based on Drupal and developed to help agencies create modern, affordable and responsive websites, whilst making it easier to collaborate and innovate. govCMS also helps reduce the technology and compliance burden on government agencies.  govCMS is mananged by the Australian Government Department of Finance. 

## Installation - End User

A copy of govCMS8 can be installed in a number of different ways:

### Acquia Cloud ([Free](https://insight.acquia.com/free/register)/[Professional](https://www.acquia.com/cloud-pricing#hardware=c3.large&storage=25&subscription=5bc0ff4a-8bea-7ea4-b175-59ba508af636&region=ap-southeast-2)/Enterprise)

Once you have provisioned an environment (you may have to select "None" as a distribution when you first provision it), you can follow the instructions at https://docs.acquia.com/acquia-cloud/create/install and in the `Install Drupal from URL` dialog, enter the URL:
    
    https://ftp.drupal.org/files/projects/govcms8-8.x-4.x-dev-core.tar.gz

### simplytest.me

For a quick demo instance, you can launch a (24-hour only) sandbox at http://simplytest.me/project/govcms8/8.x-4.x

**[Back to top](#govcms8)**
## Installation - Developer

_For an easy, one-line, dev setup see ['Automated Default Dev Setup' wiki page](https://github.com/govCMS/govCMS8/wiki/Automated-Default-Dev-Setup)._

govCMS8 utilizes [Composer](https://getcomposer.org/) to manage its dependencies. So, before using govCMS8, make sure you have Composer installed on your machine.

For best performance, we recommend adding [Prestissimo](https://github.com/hirak/prestissimo) to your global Composer before installing govCMS8, as it enables dependencies to load in parallel, significantly reducing the install time.

    composer global require "hirak/prestissimo:^0.3"

### Via Composer Create-Project

Composer will create a new directory called MY_PROJECT containing a docroot directory with a full govCMS code base therein.

    composer create-project --stability dev --prefer-dist govcms/govcms8-project MY_PROJECT

[Composer create-project](https://getcomposer.org/doc/03-cli.md#create-project) is the same as doing a git clone, followed by a composer install.

### Installation from source

    git clone -b 1.x git@github.com:govCMS/govCMS8.git
    cd govCMS8
    composer install
    
To develop on or patch against govCMS8, the source files should be downloaded and the project built.

### Other Platforms

Additional platform instructions are available in the wiki (https://github.com/govcms/govCMS8/wiki).  If you can contribute more methods, please let us know.

**[Back to top](#govcms8)**
## Technical Overview

govCMS8 comprises a number of repositories and projects:

### [govCMS8](https://github.com/govCMS/govCMS8)
* This hosts the current development release of the govCMS8 distribution, intended for distribution development only.
* This can either be required by composer (as in govCMS8-project) or git cloned.

### [govCMS8-project](https://github.com/govCMS/govCMS8-project)
* This is a Composer based installer, intended for end-user/theme developer installation of govCMS8.
* For theme and custom module developers, this is the best way to install govCMS8.
* This is the entry point for most users to govCMS8.

### [govCMS8-UI](https://github.com/govCMS/govCMS8-ui)
* This is a [Drupal 8 theme](https://www.drupal.org/project/govcms8_ui), built into govCMS8, but usable on any Drupal 8 site.
* Anyone who wants to use, develop from, or contribute to the default govCMS8 theme should start here.

### [UI-Kit-bootstrap](https://github.com/govCMS/uikit-bootstrap)
* This is a bootstrap stylesheet based on the DTA's [UI-Kit 2.0](https://github.com/govau/uikit/) that is the basis for govCMS8-UI.
* Developers looking to add or improve functionality to themes using the stylesheet should start here.

**[Back to top](#govcms8)**
## Server Requirements

* Apache, Nginx, Microsoft IIS or any other web server with proper PHP support
* MySQL 5.5.3/MariaDB 5.5.20/Percona Server 5.5.8 or higher with PDO and an InnoDB-compatible primary storage engine
* PostgreSQL 9.1.2 or higher with PDO
* SQLite 3.7.11 or higher
* PHP Version 7.1.* or higher
* [Git](http://git-scm.com/)
* [Composer](https://getcomposer.org/)

**[Back to top](#govcms8)**
## Troubleshooting and Contributing

If you're encountering some oddities, [here's a list of resolutions](https://github.com/govCMS/govCMS8/wiki/Troubleshooting) to some of the problems you may be experiencing.

### Contributing to govCMS

All contributions to govCMS8 are welcome. Issues and pull requests may be submitted against the relevant govCMS8 project on github where they will be addressed by the govCMS team.

### Patching govCMS

Because govCMS is a [Drupal distribution](https://www.drupal.org/documentation/build/distributions), modules and configurations are not added directly to the codebase. Rather, they are referenced within the composer.json file.

Any alterations to Drupal core or contributed modules must have an associated [drupal.org](https://www.drupal.org) issue filed against the project in question. Modifications should be made directly to the project in question and patched into govCMS rather than made directly against govCMS.

It is a requirement for any patches to govCMS8 to pass all automated testing prior to manual review. The automated testing checks for PHP syntax, coding standards, build completion and runs behavioural tests. It is also desirable that additions to the codebase add behat tests to ensure no regressions occur once committed.

To submit a patch, the govCMS8-core project should be forked and changes applied to a branch on the forked repository. Once all changes are applied, a pull request between govCMS8-core and the branch of the fork may be created.

**[Back to top](#govcms8)**
