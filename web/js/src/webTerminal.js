export default async function initWebTerminal() {
	const container = document.querySelector('#web-terminal');
	if (!container) {
		return;
	}

	const Terminal = await loadXterm();
	const terminal = new Terminal();
	terminal.open(container);
	terminal.write('Connecting to server...');

	const socket = new WebSocket(`wss://${window.location.host}/_shell/`);
	socket.addEventListener('open', (_) => {
		terminal.reset();
		terminal.onData((data) => socket.send(data));
		socket.addEventListener('message', (evt) => terminal.write(evt.data));
	});
	socket.addEventListener('error', (_) => {
		terminal.reset();
		terminal.write('Connection error');
	});
	socket.addEventListener('close', (_) => {
		terminal.reset();
	});
}

/** @returns {Promise<typeof import("xterm").Terminal>} */
async function loadXterm() {
	// NOTE: String expression used to prevent ESBuild from resolving
	// the import on build (xterm is a separate bundle)
	const xtermBundlePath = '/js/dist/xterm.min.js';
	const xtermModule = await import(`${xtermBundlePath}`);
	return xtermModule.Terminal;
}
