# Azure Laravel Artisan Automation - Quick Reference

## ✅ What's Automated Now

Your Azure App Service will now **automatically run** these Laravel artisan commands:

### 🔄 On Every App Restart (startup.sh)
The `startup.sh` script runs automatically whenever your Azure App Service starts or restarts:
- ✅ `php artisan storage:link`
- ✅ `php artisan migrate --force`
- ✅ `php artisan config:cache`
- ✅ `php artisan route:cache`
- ✅ `php artisan view:cache`
- ✅ `php artisan optimize`

### 🚀 After Every Deployment (deploy.sh)
The `deploy.sh` script runs immediately after each GitHub Actions deployment:
- ✅ `php artisan storage:link --force`
- ✅ `php artisan migrate --force --no-interaction`
- ✅ `php artisan config:cache`
- ✅ `php artisan route:cache`
- ✅ `php artisan view:cache`
- ✅ `php artisan optimize`

## 📝 Files Created

1. **`startup.sh`** - Runs on every app service restart
2. **`deploy.sh`** - Runs after each deployment via Azure CLI SSH
3. **`web.config`** - Configures Azure IIS for Laravel (if using Windows App Service)
4. **`.deployment`** - Azure deployment configuration

## 🎯 How It Works

### During GitHub Actions Build:
1. Scripts are made executable with `chmod +x`
2. Scripts are included in the deployment artifact
3. Artifact is uploaded to Azure

### During Deployment:
1. Artifact is deployed to Azure App Service
2. Azure CLI configures `startup.sh` as the startup command
3. Azure CLI runs `deploy.sh` via SSH immediately after deployment
4. If SSH is unavailable, falls back to individual artisan commands

### On App Service Restart:
1. Azure App Service executes `startup.sh` automatically
2. All Laravel setup commands run before app starts serving requests

## 🛠️ Manual Execution (If Needed)

If you need to run the commands manually or if SSH is not available:

### Option 1: Azure Console (Kudu)
1. Go to Azure Portal → Your App Service
2. Click "Development Tools" → "SSH" or "Console"
3. Navigate to `/home/site/wwwroot`
4. Run: `bash deploy.sh`

### Option 2: Individual Commands
```bash
cd /home/site/wwwroot
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Option 3: Azure CLI from Your Computer
```bash
az webapp ssh --name eperolehan --resource-group eperolehan_group --command "cd /home/site/wwwroot && bash deploy.sh"
```

## ⚠️ Important Notes

1. **Resource Group Name**: The workflow uses `eperolehan_group` - make sure this matches your actual Azure resource group name
   - Update in the workflow YAML if different

2. **App Service Plan**: SSH commands require a Standard (S1) or higher plan
   - If you're on Basic (B1) or Free, SSH might not be available
   - The workflow has fallbacks in place

3. **Environment Variables**: Ensure these are set in Azure App Service Configuration:
   - `APP_KEY`
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `DB_*` (all database credentials)

4. **First Deployment**: The first deployment might take longer as it sets everything up
   - Subsequent deployments will be faster

5. **Database Migrations**: Migrations run automatically with `--force` flag
   - Ensure your database is ready before deployment
   - Test migrations in a staging environment first

## 🔍 Troubleshooting

### Check if startup.sh is configured:
```bash
az webapp config show --name eperolehan --resource-group eperolehan_group --query linuxFxVersion
```

### View App Service Logs:
```bash
az webapp log tail --name eperolehan --resource-group eperolehan_group
```

### Test SSH Access:
```bash
az webapp ssh --name eperolehan --resource-group eperolehan_group
```

### View Deployment Logs:
1. Azure Portal → App Service → Deployment Center → Logs
2. Look for GitHub Actions deployment history

## 🎉 Benefits

- ✅ No manual intervention needed after deployment
- ✅ Database migrations run automatically
- ✅ Configuration always cached for optimal performance
- ✅ Storage link always available
- ✅ App optimized on every restart
- ✅ Consistent deployment process
- ✅ Reduces human error
- ✅ Faster time to production

## 📚 Next Steps

1. Commit all new files to your repository:
   ```bash
   git add startup.sh deploy.sh web.config .deployment .github/workflows/sarel-desktop_eperolehan.yml
   git commit -m "Add automated Laravel artisan commands for Azure deployment"
   git push origin sarel-desktop
   ```

2. Monitor the GitHub Actions workflow run

3. Check Azure App Service logs to verify startup script ran successfully

4. Test your application to ensure everything works correctly

