<?php

namespace SilverCommerce\Notifications\AbandonedCart;

use StatusChangeRule;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Core\Injector\Injector;
use SilverCommerce\ShoppingCart\Model\ShoppingCart;
use ilateral\SilverStripe\Notifier\Model\Notification;
use ilateral\SilverStripe\Notifier\Model\NotificationRule;
use ilateral\SilverStripe\Notifier\Types\NotificationType;

class AbandonedCartNotification extends Notification
{
    private static $table_name = "Notifications_AbandonedCartNotification";

    private static $disallow_rules = [
        NotificationRule::class,
        StatusChangeRule::class
    ];

    private static $disallow_types = [
        NotificationType::class
    ];

    public function compileSuitableBaseClasses(): array
    {
        $base_classes = ClassInfo::subclassesFor(ShoppingCart::class, true);
        $return = [];

        foreach (array_values($base_classes) as $classname) {
            /** @var DataObject */
            $obj = Injector::inst()->get($classname, true);
            $return[$classname] = $obj->i18n_singular_name();
        }

        return $return;
    }

    public function getCMSFields()
    {
        $self = $this;

        $this->beforeUpdateCMSFields(
            function ($fields) use ($self) {
                $fields->removeByName([
                    'StateCreated',
                    'StateUpdated',
                    'StateDeleted'
                ]);

                /** @var FieldList $fields */
                $fields->replaceField(
                    'BaseClassName',
                    DropdownField::create(
                        'BaseClassName',
                        $self->fieldLabel('BaseClassName'),
                        $self->compileSuitableBaseClasses()
                    )
                );
            }
        );

        return parent::getCMSFields();
    }
}
