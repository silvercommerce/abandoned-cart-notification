<?php

namespace SilverCommerce\Notifications\AbandonedCart;

use DateTime;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\Director;
use SilverCommerce\ShoppingCart\Model\ShoppingCart;

class AbandonedCartNotificationTask extends BuildTask
{
    private static $segment = 'AbandonedCartNotificationTask';

    protected $enabled = true;

    protected $title = 'Send Abandoned Cart Notifications';

    protected $description = '';

    public function run($request)
    {
        // First collect all relevent notifications
        $notifications = AbandonedCartNotification::get();

        $this->log("Processing {$notifications->count()} Abandoned Notifications");

        // Loop through each notification's rules
        foreach ($notifications as $notification) {
            $this->log("Processing notifications for: {$notification->getSummary()}");

            /**
             * @var AbandonedCartNotification $notification
             * @var TimePassedRule $rule
             */
            foreach ($notification->Rules() as $rule) {
                if (empty($rule->Value)) {
                    continue;
                }

                // Find the date relevent to this notification's rules
                $now = new DateTime();
                $now->modify("- {$rule->Value}");
                $field = $rule->FieldName;

                // Find any carts that match this date
                $carts = ShoppingCart::get()
                    ->filter([
                        $field => $now->format('Y-m-d'),
                        'Email:not' => null
                    ]);
        
                // If any carts exist, send out the relevent
                // notification via sendManually
                if (!$carts->exists()) {
                    continue;
                }

                $sent = 0;
                $skipped = 0;

                foreach ($carts as $cart) {
                    $this->log("- Sent: {$sent}; Skipped: {$skipped}", true);

                    /** @var ShoppingCart $cart */
                    foreach ($notification->Types() as $notification_type) {
                        if (!is_a($notification_type, AbandonedCartEmail::class)) {
                            $skipped++;
                            continue;
                        }

                        /** @var AbandonedCartEmail $notification_type  */
                        $notification_type->setObject($cart);
                        $notification_type->sendManually();
                        $sent++;
                    }
                }

                $this->log("- Sent: {$sent}; Skipped: {$skipped}");
            }
        }
    }

    /**
     * Log a message to the terminal/browser
     * 
     * @param string $message   Message to log
     * @param bool   $linestart Set cursor to start of line (instead of return)
     * 
     * @return null
     */
    protected function log($message, $linestart = false)
    {
        if (Director::is_cli()) {
            $end = ($linestart) ? "\r" : "\n";
            print_r($message . $end);
        } else {
            print_r($message . "<br/>");
        }
    }
}
