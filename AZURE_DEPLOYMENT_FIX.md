# Azure Deployment Fix Summary

## Issues Fixed in GitHub Actions Workflow

The Azure deployment workflow had several critical issues that prevented successful deployment:

### 1. **Missing PHP Extensions**
- Added required Laravel PHP extensions: `mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, filter, gd, json, pdo, bcmath`

### 2. **Missing Node.js and Frontend Build**
- Added Node.js 20 setup with npm caching
- Added `npm ci` to install dependencies
- Added `npm run build` to compile Vite assets (required for Laravel with Inertia.js)

### 3. **Missing Laravel Storage Directories**
- Created required Laravel directories:
  - `storage/framework/sessions`
  - `storage/framework/views`
  - `storage/framework/cache/data`
  - `storage/logs`
  - `bootstrap/cache`

### 4. **Incorrect Permissions**
- Set proper permissions (775) for storage and bootstrap/cache directories

### 5. **Composer Optimization**
- Added `--no-dev` flag to exclude development dependencies
- Added `--optimize-autoloader` for production performance

### 6. **Artifact Packaging**
- Changed from uploading entire directory to creating a zip file
- Properly excludes: `.git*`, `node_modules/*`, `tests/*`, `.env*`
- Includes: `vendor/`, `public/build/` (compiled assets), all Laravel core files

### 7. **Deployment Process**
- Added unzip step in deploy job to extract the artifact
- Added environment configuration with URL output
- Properly configured Azure login with federated credentials
- Added startup.sh script that runs on every app restart
- Added deploy.sh script for post-deployment setup
- Configured Azure to automatically run Laravel artisan commands

### 8. **Automatic Laravel Setup**
- Created `startup.sh` - runs on every app service restart
- Created `deploy.sh` - runs after each deployment
- Both scripts automatically:
  - Set proper permissions
  - Run database migrations
  - Create storage symlink
  - Cache configuration, routes, and views
  - Optimize the application
- Made scripts executable during build process
- Configured Azure App Service to use startup.sh

## What Changed in the Workflow

### Build Job Changes:
```yaml
- Setup PHP with all required extensions
- Setup Node.js v20 with npm cache
- Install Composer dependencies (production-only, optimized)
- Install NPM dependencies
- Build frontend assets with Vite
- Create Laravel required directories
- Set proper permissions
- Make deployment scripts executable (startup.sh, deploy.sh)
- Zip the artifact (excluding dev files)
- Upload zipped artifact
```

### Deploy Job Changes:
```yaml
- Download zipped artifact
- Unzip artifact for deployment
- Login to Azure with proper credentials
- Deploy to Azure Web App
- Configure startup script (runs on every app restart)
- Run post-deployment commands via SSH (migrations, caching, optimization)
```

## Automated Laravel Setup Scripts

### startup.sh (Runs on every app service restart)
This script automatically runs when Azure App Service starts/restarts:
- Sets directory permissions
- Creates storage symlink
- Runs database migrations
- Caches configuration, routes, and views
- Optimizes the application

### deploy.sh (Runs after deployment)
This script runs immediately after each deployment via SSH:
- Sets directory permissions
- Creates storage symlink
- Runs database migrations with --force flag
- Caches all Laravel configurations
- Optimizes the application for production

**Note:** If SSH is not available on your Azure plan, you can manually run these commands in the Azure Console (Kudu) or enable them through Azure App Service Configuration.

## Environment Variables to Configure in Azure

Make sure these are configured in Azure App Service Configuration:

1. **APP_KEY** - Generate with `php artisan key:generate --show`
2. **APP_ENV** - Set to `production`
3. **APP_DEBUG** - Set to `false`
4. **APP_URL** - Your Azure app URL
5. **DB_CONNECTION** - Your database connection type
6. **DB_HOST** - Your database host
7. **DB_PORT** - Your database port
8. **DB_DATABASE** - Your database name
9. **DB_USERNAME** - Your database username
10. **DB_PASSWORD** - Your database password

## Post-Deployment Commands

**Good news!** The following commands are now automated via `startup.sh` and `deploy.sh` scripts:

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

These commands will run automatically:
- After each deployment (via `deploy.sh` executed through Azure CLI SSH)
- On every app service restart (via `startup.sh` configured as startup command)

**Manual Execution (if needed):**
If SSH is not available or you need to run them manually, you can execute these in Azure Console (Kudu):
1. Go to Azure Portal → Your App Service → Development Tools → SSH or Console
2. Navigate to `/home/site/wwwroot`
3. Run the commands above or execute `bash deploy.sh`

## Testing the Deployment

1. Commit and push changes to the `sarel-desktop` branch
2. Monitor the GitHub Actions workflow run
3. Check for any errors in the build or deploy jobs
4. Once deployed, verify the app is accessible at your Azure URL
5. Check Azure App Service logs if there are any runtime issues

## Common Issues After Deployment

### Issue: White screen / 500 error
**Solution:** Check Azure logs, ensure APP_KEY is set, run migrations

### Issue: Assets not loading
**Solution:** Verify `npm run build` completed successfully, check public/build directory exists

### Issue: Database connection error
**Solution:** Verify all DB_* environment variables are set correctly in Azure

### Issue: Storage errors
**Solution:** Ensure storage directories have proper permissions, run `php artisan storage:link`

## Additional Notes

- The workflow uses OpenID Connect (OIDC) for Azure authentication (more secure than publish profiles)
- The zip artifact approach reduces deployment size and speeds up deployment
- Composer autoloader is optimized for production performance
- Frontend assets are pre-compiled during build, not at runtime

