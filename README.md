# SilverCommerce Abandoned Cart Notifications

Allows adding of configuirable reminder notifications to
your SilverCommerce store for users who have added
something to their cart, but not progressed through to
payment.

## Install

Install this module via composer:

    composer require silvercommerce/abandoned-cart-notification

Once installed run `dev/build` to add additional database tables

## Configuration and Setup

This module relies heavily on the `i-lateral/silverstripe-notifications`
module, but automatically registers it's own configuration.

### Cron Job

Reminder emails are currently sent via `AbandonedCartNotificationTask`. You can add this to a cron
job, with something like:

  0 9 * * * root /usr/bin/php /path/to/project/vendor/silverstripe/framework/cli-script.php dev/tasks/AbandonedCartNotificationTask flush=1

The above will run the task at 9am every morning.

### Middleware

If you do not want to use a cron job, the best solution would be to add some custom middleware that calls `AbandonedCartNotificationTask`. See the following docs
for more:

https://docs.silverstripe.org/en/4/developer_guides/controllers/middlewares/

## Adding reminder notifications

1. Go to `SiteConfig > Notifications`.
2. Add an `Abandoned Cart Notification`.
3. Set "Object to monitor" as "Shopping Cart" and save.
4. Add a new `Time Passed Rule`, choose field and time period.
5. Add an `Abandoned Cart Email` type and set relevent fields.

# NOTES/CAVIATS

Currently This module is currently pretty dumb, it will send notifications to ALL cart's that match the required
timeframe.

Also, the process of sendeing notifications relies on looping
through a list and sending notifications one at a time. It
might make sense to look into adding integration with some form
of external message queue.