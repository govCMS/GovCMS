api = 2
core = 8.x

; Include the definition for how to build Drupal core directly, including patches:
includes[] = drupal-org-core.make

; Download the govCMS install profile and recursively build all its dependencies:
projects[govcms8][type] = profile
projects[govcms8][download][type] = git
projects[govcms8][download][branch] = 8.x-4.x
projects[govcms8][version] = 4.x-dev