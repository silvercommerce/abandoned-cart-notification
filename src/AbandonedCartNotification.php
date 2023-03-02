<?php

namespace SilverCommerce\Notifications\AbandonedCart;

use DateTime;
use StatusChangeRule;
use SilverStripe\Forms\DropdownField;
use SilverCommerce\ShoppingCart\Model\ShoppingCart;
use ilateral\SilverStripe\Notifier\Model\Notification;
use ilateral\SilverStripe\Notifier\Model\NotificationRule;
use ilateral\SilverStripe\Notifier\Types\NotificationType;
use SilverCommerce\OrdersAdmin\Model\Estimate;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\ArrayList;

class AbandonedCartNotification extends Notification
{
    private static $table_name = "Notifications_AbandonedCartNotification";

    /**
     * Should rules and types be automatically added
     * to notifications on creation?
     *
     * @var bool
     */
    private static $auto_add_one_create = true;

    private static $carts_to_monitor = [
        ShoppingCart::class,
        Invoice::class => [
            'filter' => ['Status' => 'incomplete']
        ]
    ];

    private static $disallow_rules = [
        NotificationRule::class,
        StatusChangeRule::class
    ];

    private static $disallow_types = [
        NotificationType::class
    ];

    private static $defaults = [
        'BaseClassName' => Estimate::class
    ];

    /**
     * Create a list of classnames as keys with possible
     * filters (or empty arrays) as the values
     */
    public static function compileClassesAndFilters(): array
    {
        $classes = self::config()->get('carts_to_monitor');
        $return = [];

        foreach($classes as $key => $value) {
            if (is_array($value)) {
                $class = $key;
                $filter = $value;
            } else {
                $class = $value;
                $filter = [];
            }

            if (isset($filter['filter'])) {
                $filter = $filter['filter'];
            }

            $return[$class] = $filter;
        }

        return $return;
    }

    /**
     * Manual selection of monitored object is disabled,
     * as this is handled via seperate task
     */
    public function compileSuitableBaseClasses(): array
    {
        return [];
    }

    /**
     * Filter out any classes with default filters and
     * return a list of classnames to monitor
     */
    public function compileSuitableCartClasses(): array
    {
        $classes = self::compileClassesAndFilters();
        return array_keys($classes);
    }

    /**
     * Generate a custom list of monitored objects
     */
    public function getObjectType(): string
    {
        $classes = $this->compileSuitableCartClasses();

        foreach($classes as $class) {
            $types[] = singleton($class)->i18n_singular_name();
        }

        return implode(', ', $types);
    }

    public function getCMSFields()
    {
        $self = $this;

        $this->beforeUpdateCMSFields(
            function ($fields) use ($self) {
                $fields->removeByName([
                    'BaseClassName',
                    'StateCreated',
                    'StateUpdated',
                    'StateDeleted'
                ]);

                $fields->addFieldToTab(
                    'Root.Main',
                     ReadonlyField::create('ObjectType'),
                     'Rules'
                );

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

    protected function setupDefaultRule(): NotificationRule
    {
        $rule = TimePassedRule::create();
        $rule->NotificationID = $this->ID;

        $fields = $rule->getValidFields();
        $fields = array_keys($fields);

        if (count($fields) > 0) {
            $rule->FieldName = reset($fields);
        }

        return $rule;
    }

    protected function setupDefaultTask(): NotificationType
    {
        $task = AbandonedCartEmail::create();
        $task->NotificationID = $this->ID;
        return $task;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // If object just created and auto setup
        // add a default rule and type
        $created = $this->isChanged('ID');
        $setup = $this->config()->get('auto_add_one_create');

        if ($setup === true && $created === true) {
            $rule = $this->setupDefaultRule();
            $rule->write();

            $task = $this->setupDefaultTask();
            $task->write();
        }
    }
}
