<?php

namespace SilverCommerce\Notifications\AbandonedCart;

use SilverStripe\Forms\FieldList;
use ilateral\SilverStripe\Notifier\Model\NotificationRule;

class TimePassedRule extends NotificationRule
{
    private static $table_name = 'Notifications_TimePassedRule';

    private static $db = [
        'TimeUnit' => 'Int',
        'TimePeriod'=> 'Enum("Days,Weeks,Months","Days")'
    ];

    /**
     * Fields on an estimate/cart that can be used to trigger
     * a time based notification
     *
     *  @var array
     */
    private static $possible_fields = [
        'Created',
        'LastEdited',
        'StartDate',
        'EndDate'
    ];

    public function getSummary(): string
    {
        $results = [];
        $results[] = $this->FieldName . ":";
        $results[] = $this->TimeUnit;
        $results[] = $this->TimePeriod;

        return implode(' ', $results);
    }

    /**
     * Overwrite list of valid field names so that we only use
     * valid fields
     *
     * @return array
     */
    public function getValidFields(): array
    {
        $fields = parent::getValidFields();
        $allowed = $this->config()->get('possible_fields');

        foreach ($fields as $name => $title) {
            if (!in_array($name, $allowed)) {
                unset($fields[$name]);
            }
        }

        return $fields;
    }

    public function getCMSFields()
    {
        $self = $this;

        $this->beforeUpdateCMSFields(
            function (FieldList $fields) use ($self) {
                $fields->removeByName([
                    'WasChanged',
                    'Value'
                ]);
            }
        );

        return parent::getCMSFields();
    }

    /**
     * Push a PHP datetime modifier string to
     * value
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $modifier = "{$this->TimeUnit} {$this->TimePeriod}";
        $this->Value = $modifier;
    }
}
