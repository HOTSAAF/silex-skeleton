# Silex Skeleton Project
[![Project Status](http://stillmaintained.com/ZeeCoder/silex-skeleton.png)](http://stillmaintained.com/ZeeCoder/silex-skeleton)

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
Check out the `./bin/console app:deploy` command.
