DrushExtension
---

This extension provides integration with Drupal CMS projects, via the Drush
command-line utility.  It provides:

* DrushAwareInterface, which provides a Drush object for you contexts, useful
  for setting up test conditions, even when the system-under-test is remote.
* DrushMinkContext, which provides some useful step definitions for common
  Drupal functions, such as creating users and logging in.

Installation
---

This extension requires:

* [Behat 2.4+](http://behat.org/)
* [MinkExtension](http://extensions.behat.org/mink/)

### Through Composer

1. Define dependencies in your composer.json:
```javascript
   {
       "require": {
           ...
           "phase2/drush-extension": "*"
       },
       "repositories": [
         ...
         {
           "type": "vcs",
           "url": "https://github.com/phase2/DrushExtension"
         }
       ]
   }
```
   
2. Install/update your vendors
```
   $ curl http://getcomposer.org/installer | php
   $ php composer.phar install
```
3. Activate extension in your behat.yml
```yml
   default:
     # ...
       extensions:
         Behat\DrushExtension\Extension: ~
```

Usage
---

After installing extension, there are 2 usage options available for you:

1. Implementing `Behat\DrushExtension\Context\DrushAwareInterface` with your
   context or its subcontexts. This will give you the flexibility to inherit
   from any Context object, but also get an initialized Drush object set in
   your context.
2. Extend `Behat\DrushExtension\Context\DrushMinkContext` with your context or
   subcontext.  This context is an implementation of the `DrushAwareInterface`
   and also and extension of `MinkContext`.  You will get all of the Mink step
   definitions, all of the Drush step definitions, and access to an initialized
   Drush object.

Both of these methods will implement the `setDrush(Drush $drush)` method. This
method would be automatically called immediately after each context creation
before each scenario, initialized with the parameters set in your `behat.yml` file.

Configuration
---

DrushExtension comes with a flexible configuration system, that gives you the
ability to configure how Drush is used.

* `drush_alias` - specifies the drush alias to use from the Drush object.
  Consider this the back-end equivalent to the `base_url` defined by the
  `MinkExtension`.  Both of these settings should point to the same system.
* `config_file` - specify a `drushrc.php` file to be used whenever Drush issues
  a command.  Almost every drush option can be specified in a `drushrc.php` file
  so there should be very little that you can't manipulate using this option.
