# GovCMS 8 UI-Kit Starter

This theme implements UI Kit into GovCMS. It makes use of the templates provided 
in govcms8_uikit and extends them to work with Drupal. It also provide styling 
for the layout templates and modifiers from _govcms8_foundations_ module.

## Getting up and running
1. Run command `npm run setup`. This may take a while – be patient, do some exercise.
2. Copy and rename *default.config-local.json* to *config-local.json*.
3. Update content of config.local.json to suite your local environment.

## Workflow options

### Production setup

* run command `gulp`

**This will produce:**
* compressed CSS output
* optimized Image assets
* minified JS

### Development setup

* run command `gulp dev`

**This will produce:**
* nested CSS output
* un-minified JS
* source maps for both CSS and JS
* watch task for changes in SCSS and JS files
* BrowserSync links

### JS Linting

* run command `gulp js-lint`

This will check for common errors in your JS files.
Its not a part of the watch task.

### Generating Styleguide

* run command `npm run styleguide`

It is using KSS-Node style-guide with custom twig template.
