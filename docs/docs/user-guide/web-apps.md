# Web apps (Node.js, Python)

A web domain can run a long-running application instead of (or next to) static files and PHP: a Node.js server, a Python app (Flask, Django, FastAPI, …) or any other command such as a Bun script or a compiled Go binary.

Hestia runs the application as a **systemd service under your user**, restarts it when it crashes, starts it on boot, and points the domain at it through nginx. SSL, Let’s Encrypt, aliases, statistics and suspension keep working as usual.

## Add an app

1. Navigate to the **Web** tab and click the <i class="fas fa-cube"><span class="visually-hidden">app</span></i> **App** button next to the domain, or click **Apps** → **Add App**.
2. Choose the **runtime**:
   - **Node.js**: uses the server’s Node.js or a version your administrator installed. The default start command is `npm start`.
   - **Python**: runs inside a virtual environment (`.venv`) in the app folder.
   - **Custom command**: any command, for example `./server` or `bun run index.ts`.
3. Enter the **start command**, for example `node server.js`, `.venv/bin/gunicorn -b 127.0.0.1:$PORT app:app` or `.venv/bin/uvicorn main:app --host $HOST --port $PORT`.
4. Optionally change the **folder** (default `app`, i.e. `/home/user/web/example.com/app`) and the **install command**.
5. Click **Save**.

Upload your code to the app folder with the File Manager, SFTP or Git, then click **Install dependencies** and **Start**.

::: warning
Your application must listen on `127.0.0.1` and the port in the `PORT` environment variable. Hestia sets `PORT` and `HOST=127.0.0.1` for you. The domain returns `502 Bad Gateway` until the app listens on that port.
:::

```js
// Node.js
require("http")
	.createServer((req, res) => res.end("Hello"))
	.listen(process.env.PORT, process.env.HOST);
```

```python
# Python (Flask): start command ".venv/bin/gunicorn -b 127.0.0.1:$PORT app:app"
from flask import Flask
app = Flask(__name__)

@app.get("/")
def index():
    return "Hello"
```

## Install dependencies and build

**Install dependencies** runs, as your user, in the app folder:

- **Node.js**: `npm ci` when a `package-lock.json` exists, otherwise `npm install`.
- **Python**: creates `.venv` when missing, then `pip install -r requirements.txt` (or `pip install .` for a `pyproject.toml`).

Set your own **install command** to build the app as well, for example `npm ci && npm run build`, `pnpm install && pnpm build` or `pip install -r requirements.txt && python manage.py migrate`. The output is shown in the panel. The command stops after 30 minutes.

## Start, stop and monitor

The **Apps** page shows every app with its state, PID, CPU and memory usage, uptime and restart count, with buttons to start, stop and restart. A crashed app is restarted automatically. A stopped app stays stopped after a reboot.

The app page also shows the **logs** (everything the app writes to stdout and stderr).

### Restart from a script

To restart the app without the panel, for example at the end of a deploy script or in a Git hook, touch the file `tmp/restart.txt` in the app folder:

```bash
cd ~/web/example.com/app && git pull && npm ci && mkdir -p tmp && touch tmp/restart.txt
```

This needs no root access or SSH (uploading the file over SFTP works too). It only restarts a running app: an app you stopped, or the app of a suspended domain, stays stopped.

## Environment variables

Edit the **Environment variables** box in `.env` format and click **Save**; the app is restarted to apply them.

```bash
DATABASE_URL="mysql://user_db:secret@localhost/user_db"
SECRET_KEY='keep-this-private'
# comments and empty lines are allowed
export NODE_OPTIONS=--max-old-space-size=512
```

Values are passed to the app exactly as written: `$VAR` is not expanded. `PORT` is managed by Hestia and can’t be set. Node.js apps get `NODE_ENV=production` unless you override it.

## Delete an app

Click **Delete** on the app page. The service stops and the domain goes back to its previous template. **Your files in the app folder are kept.**

## Limits and notes

- One app per web domain. Use another domain or subdomain for a second app.
- When the server uses resource limits, apps share the CPU and memory limits of your package.
- The domain can’t be renamed and its proxy template can’t be changed while it has an app; delete the app first.
- Suspending the domain stops the app; unsuspending starts it again.
- Backups include the app files, its settings and its environment variables. Restoring the domain (also from restic snapshots) registers the app again and starts it if it was running. If its Node.js version isn’t installed on the new server, the system version is used.

## Command line

| Command                                                                                 | Description                           |
| --------------------------------------------------------------------------------------- | ------------------------------------- |
| `v-add-web-domain-app USER DOMAIN RUNTIME [VERSION] [CMD] [DIR] [INSTALL]`              | Add an app                            |
| `v-change-web-domain-app USER DOMAIN RUNTIME [VERSION] [CMD] [DIR] [INSTALL]`           | Change runtime, command or folder     |
| `v-delete-web-domain-app USER DOMAIN`                                                   | Delete the app (files are kept)       |
| `v-start-web-domain-app`, `v-stop-web-domain-app`, `v-restart-web-domain-app`           | Control the service                   |
| `v-list-web-domain-apps USER [FORMAT]`, `v-list-web-domain-app USER DOMAIN`             | Settings and live state               |
| `v-list-web-domain-app-env USER DOMAIN`, `v-change-web-domain-app-env USER DOMAIN FILE` | Environment variables                 |
| `v-list-web-domain-app-log USER DOMAIN [LINES]`                                         | Recent log lines                      |
| `v-update-web-domain-app-deps USER DOMAIN`                                              | Run the install command               |
| `v-add-sys-nodejs VERSION`, `v-delete-sys-nodejs VERSION`, `v-list-sys-nodejs`          | Manage extra Node.js versions (admin) |

Administrators can install additional Node.js major versions with `v-add-sys-nodejs 22`; they are downloaded from nodejs.org, verified against the official checksums and installed in `/opt/hestia/nodejs/22`. Apps listen on ports 30000–39999 by default; set `APP_PORT_MIN` and `APP_PORT_MAX` in `hestia.conf` to change the range.
