<?php

namespace SilverCommerce\Notifications\AbandonedCart;

use SilverStripe\Forms\FieldList;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Email\Email;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\LinkField\Form\LinkField;
use SilverCommerce\ShoppingCart\Model\ShoppingCart;
use ilateral\SilverStripe\Notifier\Types\EmailNotification;

class AbandonedCartEmail extends EmailNotification
{
    private static $table_name = "Notifications_AbandonedCartEmail";

    private static $singular_name = 'Abandoned Cart Email';

    private static $plural_name = 'Abandoned Cart Emails';

    private static $template = self::class;

    private static $has_one = [
        'Link' => Link::class
    ];

    private static $alt_recipient_fields = [
        ShoppingCart::class => ['Customer.Email']
    ];

    public function populateDefaults()
    {
        $sender = Config::inst()->get(Email::class, 'admin_email');
        $this->From = $sender;
        $this->AltRecipient = 'Customer.Email';
    }

    /**
     * Technically a stub method, as send is managed via
     * @link AbandonedCartNotificationsTask
     */
    public function send(
        array $custom_recipients = [],
        array $custom_data = []
    ) {
        return;
    }

    /**
     * Manually call send (expected to be called via
     * @link AbandonedCartNotificationsTask)
     */
    public function sendManually(
        array $custom_recipients = [],
        array $custom_data = []
    ) {
        $custom_data['Link'] = $this->Link();
        return parent::send($custom_recipients, $custom_data);
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'Recipient',
                'LinkID'
            ]);

            /** @var FormField */
            $alt_recipient_field = $fields->dataFieldByName('AltRecipient');

            if (!empty($alt_recipient_field)) {
                $alt_recipient_field
                    ->setTitle($this->fieldLabel('Recipient'));
            }

            $fields->insertAfter(
                'Content',
                LinkField::create('Link')
            );
        });

        return parent::getCMSFields();
    }
}
