<?php

return [

    // Emails allowed to open the Audit Trail (comma separated in .env).
    // Users with role "admin" can always view it. If this list is EMPTY,
    // anyone who can open the admin panel can view it.
    'viewer_emails' => array_values(array_filter(array_map(
        fn ($email) => strtolower(trim($email)),
        explode(',', (string) env('AUDIT_VIEWER_EMAILS', ''))
    ))),

    // `php artisan audit:prune` deletes entries older than this many days.
    'retention_days' => (int) env('AUDIT_RETENTION_DAYS', 365),

    // Sensitive attributes: never stored, but a change is recorded as "[changed]".
    'redact' => ['password', 'two_factor_secret', 'two_factor_recovery_codes'],

    // Pure noise: never stored and never recorded as a change.
    'ignore' => ['remember_token', 'updated_at'],
];
