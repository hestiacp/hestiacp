// Prevents double submission of the login form.
//
// Root cause: on a successful login the server calls session_regenerate_id(true),
// which deletes the old session (and its CSRF token). A second request sent
// before the browser applies the new session cookie arrives with the deleted
// session, fails verify_csrf(), and falls through to rendering the username
// screen ("Welcome to Hestia Control Panel").
//
// Approach: let the first submit proceed natively, then preventDefault on every
// subsequent submit while the navigation is in flight. A re-enable timer is
// (re)started on each suppressed submit, so a user who keeps clicking only
// re-enables the button 1.5s after their *last* click. If the navigation
// commits, the page unloads and the timer never fires.
export default function handleLoginFormSubmit() {
	const loginForm = document.querySelector('#login-form');
	if (!loginForm) {
		return;
	}

	const submitButtons = () =>
		loginForm.querySelectorAll('button[type="submit"], input[type="submit"]');

	const setDisabled = (disabled) => {
		submitButtons().forEach((button) => {
			button.disabled = disabled;
		});
	};

	let submitted = false;
	let reenableTimer = null;

	const REENABLE_DELAY = 1500;

	loginForm.addEventListener('submit', (event) => {
		if (!submitted) {
			// First submit: allow it through and lock the form.
			submitted = true;
			setDisabled(true);
			// Start a fallback timer in case the navigation is cancelled
			// (Esc / stop) so the user is not permanently locked out.
			reenableTimer = setTimeout(() => {
				submitted = false;
				setDisabled(false);
			}, REENABLE_DELAY);
			return;
		}

		// Subsequent submit while the first one is still in flight:
		// suppress it and reset the re-enable timer so the lock only
		// releases 1.5s after the user stops clicking.
		event.preventDefault();
		if (reenableTimer) {
			clearTimeout(reenableTimer);
		}
		reenableTimer = setTimeout(() => {
			submitted = false;
			setDisabled(false);
		}, REENABLE_DELAY);
	});
}
