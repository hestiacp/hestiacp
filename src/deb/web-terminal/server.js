#!/usr/bin/env node

import { execFileSync, execSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { spawn } from 'node-pty';
import { WebSocketServer } from 'ws';

const sessionName = 'HESTIASID';
const hostname = execSync('hostname', { silent: true }).toString().trim();
const systemIPs = JSON.parse(
	execSync(`${process.env.HESTIA}/bin/v-list-sys-ips json`, { silent: true }).toString(),
);
const { config } = JSON.parse(
	execSync(`${process.env.HESTIA}/bin/v-list-sys-config json`, { silent: true }).toString(),
);

function parseCookies(cookieHeader) {
	const cookies = {};
	if (typeof cookieHeader !== 'string' || cookieHeader.length === 0) {
		return cookies;
	}

	for (const part of cookieHeader.split(';')) {
		const cookie = part.trim();
		if (cookie.length === 0) {
			continue;
		}

		const separatorIndex = cookie.indexOf('=');
		if (separatorIndex < 0) {
			cookies[cookie] = cookies[cookie] || [];
			cookies[cookie].push('');
			continue;
		}

		if (separatorIndex === 0) {
			continue;
		}

		const key = cookie.slice(0, separatorIndex).trim();
		const value = cookie.slice(separatorIndex + 1).trim();
		if (key.length === 0) {
			continue;
		}

		cookies[key] = cookies[key] || [];
		cookies[key].push(value);
	}

	return cookies;
}

const wss = new WebSocketServer({
	port: Number.parseInt(config.WEB_TERMINAL_PORT, 10),
	verifyClient: async (info, cb) => {
		const cookies = parseCookies(info.req.headers.cookie);
		const sessionIDs = cookies[sessionName] || [];
		if (sessionIDs.length !== 1 || sessionIDs[0].length === 0) {
			cb(false, 401, 'Unauthorized');
			return;
		}

		const origin = info.origin || info.req.headers.origin;
		let matches = origin === `https://${hostname}:${config.BACKEND_PORT}`;

		if (!matches) {
			for (const ip of Object.keys(systemIPs)) {
				if (origin === `https://${ip}:${config.BACKEND_PORT}`) {
					matches = true;
					break;
				}
			}
		}

		if (matches) {
			cb(true);
			return;
		}
		cb(false, 403, 'Forbidden');
	},
});

wss.on('connection', (ws, req) => {
	wss.clients.add(ws);

	const remoteIP = req.headers['x-real-ip'] || req.socket.remoteAddress;

	// Check if session is valid
	const cookies = parseCookies(req.headers.cookie);
	const sessionIDs = cookies[sessionName] || [];
	if (sessionIDs.length !== 1 || sessionIDs[0].length === 0) {
		console.error(`Missing ${sessionName} cookie from ${remoteIP}, refusing connection`);
		ws.close(1000, 'You are not authenticated.');
		return;
	}
	const sessionID = sessionIDs[0];
	console.log(`New connection from ${remoteIP} (${sessionID})`);

	let authResult;
	try {
		const raw = execFileSync(
			`${process.env.HESTIA}/php/bin/php`,
			[`${process.env.HESTIA}/web-terminal/web-terminal-session-auth.php`, sessionID],
			{ encoding: 'utf8' },
		);
		authResult = JSON.parse(raw);
	} catch (error) {
		console.error(`Session helper failed for ${sessionID}, refusing connection: ${error.message}`);
		ws.close(1000, 'Your session has expired.');
		return;
	}

	if (!authResult?.ok || typeof authResult.user !== 'string' || authResult.user.length === 0) {
		console.error(`Unauthenticated session ${sessionID}, refusing connection`);
		ws.close(1000, 'You are not authenticated.');
		return;
	}
	const login = authResult.user;
	const impersonating = typeof authResult.look === 'string' ? authResult.look : '';
	const username = impersonating.length > 0 ? impersonating : login;

	// Get user info
	const passwd = readFileSync('/etc/passwd').toString();
	const userline = passwd.split('\n').find((line) => line.startsWith(`${username}:`));
	if (!userline) {
		console.error(`User ${username} not found, refusing connection`);
		ws.close(1000, 'You are not allowed to access this server.');
		return;
	}
	const [, , uid, gid, , homedir, shell] = userline.split(':');

	if (shell.endsWith('nologin')) {
		console.error(`User ${username} has no shell, refusing connection`);
		ws.close(1000, 'You have no shell access.');
		return;
	}

	// Spawn shell as logged in user
	const pty = spawn(shell, [], {
		name: 'xterm-color',
		uid: Number.parseInt(uid, 10),
		gid: Number.parseInt(gid, 10),
		cwd: homedir,
		env: {
			SHELL: shell,
			TERM: 'xterm-color',
			USER: username,
			HOME: homedir,
			PWD: homedir,
			HESTIA: process.env.HESTIA,
		},
	});
	console.log(`New pty (${pty.pid}): ${shell} as ${username} (${uid}:${gid}) in ${homedir}`);

	// Send/receive data from websocket/pty
	pty.on('data', (data) => ws.send(data));
	ws.on('message', (data) => pty.write(data));

	// Ensure pty is killed when websocket is closed and vice versa
	pty.on('exit', () => {
		console.log(`Ended pty (${pty.pid})`);
		if (ws.OPEN) {
			ws.close();
		}
	});
	ws.on('close', () => {
		console.log(`Ended connection from ${remoteIP} (${sessionID})`);
		pty.kill();
		wss.clients.delete(ws);
	});
});
