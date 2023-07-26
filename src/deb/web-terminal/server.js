#!/usr/bin/env node

import { execSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { userInfo } from 'node:os';
import { seteuid } from 'node:process';
import { spawn } from 'node-pty';
import { WebSocketServer } from 'ws';

const hostname = execSync('hostname', { silent: true }).toString().trim();
const hestia = JSON.parse(execSync('v-list-sys-config json', { silent: true }).toString());

const allowedOrigin = `https://${hostname}:${hestia.config.BACKEND_PORT}`;

const wss = new WebSocketServer({
	port: 8085,
	verifyClient: async (info, cb) => {
		if (!info.req.headers.cookie.includes('PHPSESSID')) {
			cb(false, 401, 'Unauthorized');
			return;
		}
		const origin = info.origin || info.req.headers.origin;
		if (origin === allowedOrigin) {
			cb(true);
			return;
		}
		cb(false, 403, 'Forbidden');
	},
});

wss.on('connection', (ws, req) => {
	console.log('A new client connected.');

	// Get user info from PHP session
	const session = readFileSync(
		`${process.env.HESTIA}/data/sessions/sess_${req.headers.cookie.split('=')[1]}`
	);
	if (!session) {
		console.log('No session found.');
		ws.close();
		return;
	}

	const username = session.toString().split('user|s:')[1].split('"')[1];
	const impersonating = session.toString().split('look|s:')[1].split('"')[1];
	const oldUid = process.getuid();
	seteuid(impersonating.length > 0 ? impersonating : username);
	const user = userInfo();
	seteuid(oldUid);

	const pty = spawn('bash', [], {
		name: 'xterm-color',
		uid: user.uid,
		gid: user.gid,
		cwd: user.homedir,
		env: {
			SHELL: '/bin/bash',
			TERM: 'xterm-color',
			USER: user.username,
			HOME: user.homedir,
			PWD: user.homedir,
			HESTIA: process.env.HESTIA,
		},
	});

	pty.on('data', (data) => ws.send(data));
	ws.on('message', (data) => pty.write(data));

	pty.on('exit', () => ws.close());
	ws.on('close', () => {
		pty.kill();
		console.log('Client disconnected.');
	});
});

console.log('WebSocket server is running on port 8085.');
