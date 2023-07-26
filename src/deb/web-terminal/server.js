#!/usr/bin/env node

import { execSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { spawn } from 'node-pty';
import { WebSocketServer } from 'ws';

const hostname = execSync('hostname', { silent: true }).toString().trim();
const { config } = JSON.parse(execSync('v-list-sys-config json', { silent: true }).toString());

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
	// Check if session is valid
	const session_id = req.headers.cookie.split('=')[1];
	const file = readFileSync(`${process.env.HESTIA}/data/sessions/sess_${session_id}`);
	if (!file) {
		ws.close();
		return;
	}
	const session = file.toString();

	// Get username
	const login = session.split('user|s:')[1].split('"')[1];
	const impersonating = session.split('look|s:')[1].split('"')[1];
	const username = impersonating.length > 0 ? impersonating : login;

	// Get user info
	const passwd = readFileSync('/etc/passwd').toString();
	const userline = passwd.split('\n').find((line) => line.startsWith(`${username}:`));
	if (!userline) {
		ws.close();
		return;
	}
	const [, , uid, gid, , homedir] = userline.split(':');

	// Spawn shell as logged in user
	const pty = spawn('bash', [], {
		name: 'xterm-color',
		uid: uid,
		gid: gid,
		cwd: homedir,
		env: {
			SHELL: '/bin/bash',
			TERM: 'xterm-color',
			USER: username,
			HOME: homedir,
			PWD: homedir,
			HESTIA: process.env.HESTIA,
		},
	});

	// Send/receive data from websocket/pty
	pty.on('data', (data) => ws.send(data));
	ws.on('message', (data) => pty.write(data));

	// Ensure pty is killed when websocket is closed and vice versa
	pty.on('exit', () => ws.close());
	ws.on('close', () => pty.kill());
});
