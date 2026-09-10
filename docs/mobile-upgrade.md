# Mobile and package update

## Packages

- `hammadzafar05/filament-mobile-preset` replaces the direct bottom-nav dependency and registration. The preset still requires `hammadzafar05/mobile-bottom-nav` internally. Dashboard, Observations, and More are available at the bottom of mobile screens.
- `ysfkaya/filament-shiplog` reads `CHANGELOG.md` and displays it under What's new. Only signed-in users can read it; cache management is restricted to developers.
- `martin6363/filament-click-spark` adds feedback to action buttons and respects reduced motion preferences.
- `yousefaman/filament-autosave` saves new observation drafts after two seconds of inactivity. Drafts are private to the user and expire after 24 hours. Restore or discard a draft when returning to the create page. Uploaded photos must be reattached. Existing-record edits still use the normal validated Save workflow.

Filament, Livewire, Laravel, and compatible dependencies were updated in `composer.lock`. The missing Pusher PHP server dependency was added for the application's configured broadcaster.

## Performance and fixes

Presentation pages retrieve one observation per slide instead of loading the full dataset repeatedly. Mention indicators reuse query results and loaded relationships instead of querying comments for each table cell. Dashboard statistics poll every 60 seconds. Presentation images and table avatars load lazily.

Date filters include the entire final day. Profile editing and registration now provide the required username. Missing logos have a text fallback, placeholder footer links were removed, and the public debug endpoint was removed.

## Setup on another environment

Use PHP 8.4 or later to satisfy the locked dependencies, then run:

```sh
composer install
npm ci
npm run build
php artisan filament:assets
php artisan view:clear
```

These plugins use the existing cache and do not require new application tables. Keep the public storage disk and its existing uploads available. The local checkout did not contain the referenced logo and observation photo files.

## Verification

- 45 automated tests passed, including draft recovery and user isolation, date boundaries, presentation pagination, and mention-query checks.
- Production assets built successfully; Blade templates compiled successfully.
- Composer audit reported no advisories.
- Signed in using the supplied account and checked mobile layouts at 320 and 390 pixels, tablet presentation at 768 pixels, and desktop observations at 1440 pixels.

Changes are local and have not been deployed.
