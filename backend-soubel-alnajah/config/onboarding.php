<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Onboarding Delivery Fallback
    |--------------------------------------------------------------------------
    |
    | When sending password setup/reset links fails, notify school admins via
    | database notifications so operations teams can manually intervene.
    |
    */
    'notify_admins_on_failure' => (bool) env('ONBOARDING_NOTIFY_ADMINS_ON_FAILURE', true),
];
