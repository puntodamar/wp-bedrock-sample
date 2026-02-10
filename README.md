# Bedrock + DDEV + Sage (with APP_URL undefined fix)

This guide sets up:
- **Roots Bedrock** (WordPress via Composer)
- **DDEV** (local dev)
- **Roots Sage** (theme)
  …and fixes the common Vite message: `APP_URL: undefined`.

---

## Prerequisites

Make sure these are installed:

```bash
docker --version
ddev version
composer --version
node --version
npm --version
1) Create a new Bedrock project
mkdir my-bedrock-site
cd my-bedrock-site

composer create-project roots/bedrock .
cp .env.example .env
Bedrock structure reminder:

Web root: web/

WP core: web/wp

Themes/plugins: web/app

2) Configure DDEV (use WordPress project type)
This is important so ddev wp works.

ddev config --project-type=wordpress --docroot=web --create-docroot
ddev start
3) Configure Bedrock .env for DDEV
Edit .env in the project root:

DB_NAME='db'
DB_USER='db'
DB_PASSWORD='db'
DB_HOST='db'

WP_ENV='development'
WP_HOME="${DDEV_PRIMARY_URL}"
WP_SITEURL="${DDEV_PRIMARY_URL}/wp"
4) Install Bedrock dependencies
ddev composer install
5) Install WordPress (WP tables) via WP-CLI
ddev wp core install \
  --url="$(ddev describe -j | jq -r '.raw.primary_url')" \
  --title="My Bedrock Site" \
  --admin_user=admin \
  --admin_password=admin \
  --admin_email=admin@example.com
If you don’t have jq:

ddev describe
Copy the primary URL and pass it manually to --url.

6) Install Sage theme
6.1 Create the theme inside Bedrock
From the project root:

cd web/app/themes
composer create-project roots/sage my-theme
Example:

composer create-project roots/sage teman-theme
6.2 Install PHP dependencies for the theme
cd teman-theme
composer install
6.3 Install JS dependencies for the theme
npm install
7) Fix APP_URL: undefined (Vite message)
This message is printed by Sage’s Vite/Laravel plugin banner and is often harmless, but you can remove it by setting APP_URL in the theme’s .env.

From the theme directory:

cd web/app/themes/teman-theme
Create .env if it doesn’t exist:

cp .env.example .env 2>/dev/null || touch .env
Add this line (replace with your DDEV URL):

APP_URL=https://my-bedrock-site.ddev.site
Restart Vite after editing:

npm run dev
8) Build theme assets
For development (HMR/watch):

npm run dev
For production build:

npm run build
9) Activate the Sage theme
From the project root:

cd ../../../..
ddev wp theme activate teman-theme
Or in wp-admin:
Appearance → Themes → Activate

10) Access the site
Frontend:

https://my-bedrock-site.ddev.site

Admin:

https://my-bedrock-site.ddev.site/wp/wp-admin

Quick sanity checks
ddev wp theme status
ddev wp option get home
ddev wp option get siteurl
Expected:

home → https://<project>.ddev.site

siteurl → https://<project>.ddev.site/wp