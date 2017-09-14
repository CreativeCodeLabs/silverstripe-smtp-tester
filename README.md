# SilverStripe 3 SMTP Tester

This module provides an easy way to test the SMTP configuration of your SilverStripe site from the admin area.

## Installation

##### Using Composer
```html
$ composer require creativecodelabs/silverstripe-smtp-tester
```
* Run `/dev/build?flush=1`

##### Manual Installation

* Install the contents of this repository in the root of your SilverStripe project in a directory named "silverstripe-smtp-tester".
* Run `/dev/build?flush=1`

## User Whitelist

By default, the module will be available to anyone with the "Access to SMTP Tester section" permission, including admins.

You can add an additional restriction by setting the SmtpTester.user_domain_whitelist config option in your config.yml file.

**mysite/_config/config.yml**
```html
SmtpTester:
  user_domain_whitelist: 'mycompany.com'
```

When set, the domain of the member's email address must match the whitelist in order to view the SMTP Tester module. Multiple domains can be provided in a comma-separated list.

Be sure to re-run `/dev/build?flush=1` after changing your config options.
