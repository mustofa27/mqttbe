<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Plan Hard Enforcement
    |--------------------------------------------------------------------------
    |
    | When false, quota/feature violations are logged but not blocked. This
    | supports a grace period during migrations. Set to true to enable hard
    | blocking in middleware/controllers.
    |
    */
    'plan_hard_enforce' => env('PLAN_HARD_ENFORCE', false),
];
