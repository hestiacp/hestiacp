export default async function initWebTerminal() {
	const container = document.querySelector('.js-web-terminal');
	if (!container) {
		return;
	}

	const Terminal = await loadXterm();
	const terminal = new Terminal();
	let Addon = null;
	if (typeof WebGL2RenderingContext !== 'undefined') {
		Addon = await loadWebGLAddon();
	} else {
		Addon = await loadCanvasAddon();
	}
	terminal.loadAddon(new Addon());
	terminal.open(container);

	const socket = new WebSocket(`wss://${window.location.host}/_shell/`);
	socket.addEventListener('open', (_) => {
		terminal.onData((data) => socket.send(data));
		socket.addEventListener('message', (evt) => terminal.write(evt.data));
	});
	socket.addEventListener('error', (_) => {
		terminal.reset();
		terminal.writeln('Connection error.');
	});
	socket.addEventListener('close', (evt) => {
		if (evt.wasClean) {
			terminal.reset();
			terminal.writeln(evt.reason ?? 'Connection closed.');
		}
	});
}

/** @returns {Promise<typeof import("xterm").Terminal>} */
async function loadXterm() {
	// NOTE: String expression used to prevent ESBuild from resolving
	// the import on build (xterm is a separate bundle)
	const xtermBundlePath = '/js/dist/xterm.min.js';
	const xtermModule = await import(`${xtermBundlePath}`);
	return xtermModule.default.Terminal;
}

/** @returns {Promise<typeof import("xterm-addon-webgl").WebglAddon>} */
async function loadWebGLAddon() {
	// NOTE: String expression used to prevent ESBuild from resolving
	// the import on build (xterm-addon-webgl is a separate bundle)
	const xtermBundlePath = '/js/dist/xterm-addon-webgl.min.js';
	const xtermModule = await import(`${xtermBundlePath}`);
	return xtermModule.default.WebglAddon;
}

/** @returns {Promise<typeof import("xterm-addon-canvas").CanvasAddon>} */
async function loadCanvasAddon() {
	// NOTE: String expression used to prevent ESBuild from resolving
	// the import on build (xterm-addon-canvas is a separate bundle)
	const xtermBundlePath = '/js/dist/xterm-addon-canvas.min.js';
	const xtermModule = await import(`${xtermBundlePath}`);
	return xtermModule.default.CanvasAddon;
}
