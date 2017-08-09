# govCMS (Drupal 8)

[![Build Status](https://travis-ci.org/govCMS/govcms-core.svg?branch=8.4.x)](https://travis-ci.org/govCMS/govcms-core)

## Installation

### Server Requirements

* Apache, Nginx, Microsoft IIS or any other web server with proper PHP support
* PHP 5.6.0 or higher
* MySQL 5.5.3/MariaDB 5.5.20/Percona Server 5.5.8 or higher with PDO and an InnoDB-compatible primary storage engine
* PostgreSQL 9.1.2 or higher with PDO
* SQLite 3.7.11 or higher

**Dependencies**

* [Git](http://git-scm.com/)
* [Composer](https://getcomposer.org/)

### Installing govCMS

govCMS Drupal 8 utilizes [Composer](https://getcomposer.org/) to manage its dependencies. So, before using govCMS, make sure you have Composer installed on your machine.

For a better performance, we recommend

```
composer global require "hirak/prestissimo:^0.3"
```

#### Via Composer Create-Project

```
composer create-project --stability dev --prefer-dist govcms/govcms-project MY_PROJECT
```

Composer will create a new directory called MY_PROJECT containing a docroot directory with a full govCMS code base therein.

```
cd MY_PROJECT
composer update
```

And you can update govCMS Distribution Core via
```
composer update govcms/govcms
```

#### Packaged installation

govCMS exists as packaged versions on both the [Github](https://github.com/govCMS/govCMS) and [Drupal.org](https://www.drupal.org/project/govcms) project pages. These compressed archives are available in both zip and tar.gz format to download and use as needed.

This is no longer the recommended method and will likely be deprecated in the future.

#### Installation from source

To develop on or patch against govCMS, the source files should be downloaded and the project built.

govCMS source may be downloaded using git

```
git clone -b 8.4.x git@github.com:govCMS/govcms-core.git
```

## Patching govCMS

Because govCMS is a [Drupal distribution](https://www.drupal.org/documentation/build/distributions), modules and configurations are not added directly to the codebase. Rather, they are referenced within the composer.json file.

Any alterations to Drupal core or contributed modules must have an associated [drupal.org](https://www.drupal.org) issue filed against the project in question. Modifications should be made directly to the project in question and patched into govCMS rather than made directly against govCMS.

It is a requirement for any patches to govCMS to pass all automated testing prior to manual review. The automated testing checks for PHP syntax, coding standards, build completion and runs behavioural tests. It is also desirable that additions to the codebase add behat tests to ensure no regressions occur once committed.

To submit a patch, the govCMS project should be forked and changes applied to a branch on the forked repository. Once all changes are applied, a pull request between govCMS/master and the branch of the fork may be created.


## Contributing to govCMS

All contributions to govCMS are welcome. Issues and pull requests may be submitted against the govCMS project on github where they will be addressed by the govCMS team.
