#!/usr/bin/env bats

@test "Laravel edit page is a tabbed workbench, not a single stacked form" {
	run grep -E 'class="[^"]*laravel-layout' web/templates/pages/edit_laravel.php
	[ "$status" -eq 0 ]

	run grep -E 'class="[^"]*laravel-sidebar' web/templates/pages/edit_laravel.php
	[ "$status" -eq 0 ]

	run grep -E 'class="[^"]*laravel-tabs' web/templates/pages/edit_laravel.php
	[ "$status" -eq 0 ]

	run grep -F 'aria-labelledby="tab-laravel-logs"' web/templates/pages/edit_laravel.php
	[ "$status" -eq 0 ]

	run grep -E 'class="[^"]*laravel-console' web/templates/pages/edit_laravel.php
	[ "$status" -eq 0 ]

	run grep -E 'class="[^"]*laravel-secret' web/templates/pages/edit_laravel.php
	[ "$status" -eq 0 ]

	run grep -F 'App root' web/templates/pages/edit_laravel.php
	[ "$status" -eq 0 ]

	run grep -F 'Maintenance mode' web/templates/pages/edit_laravel.php
	[ "$status" -eq 0 ]

	run grep -F 'data-label-hide' web/templates/pages/edit_laravel.php
	[ "$status" -eq 0 ]

	run grep -F 'id="command_type"' web/templates/pages/edit_laravel.php
	[ "$status" -ne 0 ]
}

@test "Laravel controller preserves focused tabs and clears transient command output" {
	run grep -F '#tab-laravel-' web/edit/laravel/index.php
	[ "$status" -eq 0 ]

	run grep -F 'laravel_command_status' web/edit/laravel/index.php
	[ "$status" -eq 0 ]

	run grep -F 'laravel_command_type' web/edit/laravel/index.php
	[ "$status" -eq 0 ]

	run grep -F 'in_array($command_type, ["artisan", "composer", "node"], true)' web/edit/laravel/index.php
	[ "$status" -eq 0 ]

	run grep -F 'Invalid Laravel action.' web/edit/laravel/index.php
	[ "$status" -eq 0 ]

	run grep -F 'laravel_env_value' web/edit/laravel/index.php
	[ "$status" -eq 0 ]
}

@test "Tab helper scopes behavior to every tab group" {
	run grep -F "document.querySelectorAll('.js-tabs')" web/js/src/tabPanels.js
	[ "$status" -eq 0 ]

	run grep -F "event.target.closest('.tabs-item')" web/js/src/tabPanels.js
	[ "$status" -eq 0 ]

	run grep -F 'tabs.querySelector(`[aria-labelledby="${tabId}"]`)' web/js/src/tabPanels.js
	[ "$status" -eq 0 ]
}

@test "Laravel list page has a useful empty state and manage action" {
	run grep -F 'No Laravel applications registered' web/templates/pages/list_laravel.php
	[ "$status" -eq 0 ]

	run grep -F 'class="button' web/templates/pages/list_laravel.php
	[ "$status" -eq 0 ]

	run grep -F 'Scheduler' web/templates/pages/list_laravel.php
	[ "$status" -eq 0 ]

	run grep -F 'Queue' web/templates/pages/list_laravel.php
	[ "$status" -eq 0 ]
}
