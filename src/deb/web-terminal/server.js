#!/usr/bin/env node

import { execSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { spawn } from 'node-pty';
import { WebSocketServer } from 'ws';

const hostname = execSync('hostname', { silent: true }).toString().trim();
const { config } = JSON.parse(
	execSync(`${process.env.HESTIA}/bin/v-list-sys-config json`, { silent: true }).toString()
);

const wss = new WebSocketServer({
	port: 8085,
	verifyClient: async (info, cb) => {
		if (!info.req.headers.cookie.includes('PHPSESSID')) {
			cb(false, 401, 'Unauthorized');
			return;
		}
		const origin = info.origin || info.req.headers.origin;
		if (origin === `https://${hostname}:${config.BACKEND_PORT}`) {
			cb(true);
			return;
		}
		cb(false, 403, 'Forbidden');
	},
});

wss.on('connection', (ws, req) => {
	wss.clients.add(ws);

	// Check if session is valid
	const session_id = req.headers.cookie.split('=')[1];
	const file = readFileSync(`${process.env.HESTIA}/data/sessions/sess_${session_id}`);
	if (!file) {
		console.error(`Invalid session ID ${session_id}`);
		ws.close();
		return;
	}
	console.log(`New connection from ${req.socket.remoteAddress} (${session_id})`);
	const session = file.toString();

	// Get username
	const login = session.split('user|s:')[1].split('"')[1];
	const impersonating = session.split('look|s:')[1].split('"')[1];
	const username = impersonating.length > 0 ? impersonating : login;

	// Get user info
	const passwd = readFileSync('/etc/passwd').toString();
	const userline = passwd.split('\n').find((line) => line.startsWith(`${username}:`));
	if (!userline) {
		console.error(`User ${username} not found`);
		ws.close();
		return;
	}
	const [, , uid, gid, , homedir, shell] = userline.split(':');

	// Spawn shell as logged in user
	const pty = spawn(shell, [], {
		name: 'xterm-color',
		uid: parseInt(uid, 10),
		gid: parseInt(gid, 10),
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
		console.log(`Ended connection from ${req.socket.remoteAddress} (${session_id})`);
		pty.kill();
		wss.clients.delete(ws);
	});
});
