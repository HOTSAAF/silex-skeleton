## Needed documentation
 - onesky command documentation
 - include a list of features in this skeleton
 - forms: builder + validation + translations + templates
 - swift mailer
 - contact_form.js module
 - ajax.js module
 - configurations, parameters.yml
 - firewall (login)
 - api service, controller, versions and exception hadnling / debugging
 - image_loader.js
 - admin boilerplate comments


## Setup
- implement the `src/config/parameters.yml.dist` file without the ".dist",
- run `composer install`,
- run `npm install`,
- run `bower install`,
- run `gulp build`
- run `bin/doctrine orm:schema-tool:create`,
- execute the sql files stored in `src/database` in order. If you set up the
  project for the first time, you can run the following command to concatenate
  everything: `cat src/database/[^_]* > src/database/_db.sql`.
  (It concatenates every file not starting with an underscore.)

## Deployment
Run `./bin/deploy deploy:<dev/prod>`.
This command will build, and deploy the application using the configurations
in `src/config/deploy_stages/`.
Assets which are accessed through the `asset()` twig function are
automatically busted.
Assets accessed through the `v-image-url` sass function must be busted
manually, by incrementing the `$g-asset-version` variable in the `_global.scss`
file.

## Afterworks on windows (at least Vista):
- Remove the `web/images` file,
- Run `git update-index --assume-unchanged web/images`,
- Run `mklink /D web\images ..\front_src\images`.
