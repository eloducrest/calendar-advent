<?php

return [
    /*
    |----------------------------------------------------------------------
    | Unlock all calendar days
    |----------------------------------------------------------------------
    |
    | When enabled (i.e. while preparing the calendar before December),
    | every day behaves as unlocked: doors are openable and challenges
    | can be solved regardless of their available date.
    |
    | CALENDAR_UNLOCK_ALL=true in the application .env file.
    |
    */
    'unlock_all' => (bool) env('CALENDAR_UNLOCK_ALL', false),
];
