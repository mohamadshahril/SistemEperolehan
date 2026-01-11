# Azure App Service GitHub Integration - Deployment Setup

## ✅ Your Current Configuration

You're using **Azure App Service Deployment Center** with **GitHub integration**, which automatically uses **publish profile** authentication.

---

## 🎯 What Changed in Your Workflows

Both workflows have been updated to use the **publish profile** method that Azure App Service GitHub integration provides:

### ✅ sarel-desktop_eperolehan.yml
- **Removed**: OIDC authentication (client-id, tenant-id, subscription-id)
- **Removed**: Azure CLI login step
- **Removed**: Post-deployment CLI commands
- **Using**: Publish profile secret: `AZUREAPPSERVICE_PUBLISHPROFILE_74D7D3AC927646EB9A54D254900E95ED`

### ✅ master_eperolehan.yml
- **Removed**: OIDC authentication (client-id, tenant-id, subscription-id)
- **Removed**: Azure CLI login step
- **Removed**: Post-deployment CLI commands
- **Using**: Publish profile secret: `AZUREAPPSERVICE_PUBLISHPROFILE_364EF80149B94C6399276BDF9FE2650A`

---

## 🔧 How Azure App Service GitHub Integration Works

### 1. **When You Connect in Azure Portal:**
```
Azure Portal 
  → App Service 
  → Deployment Center 
  → GitHub 
  → Select Repo & Branch
```

Azure automatically:
- ✅ Creates or updates the workflow YAML file
- ✅ Downloads the publish profile
- ✅ Adds it as a GitHub secret (with the long ID in the name)
- ✅ Configures the workflow to use it

### 2. **The Publish Profile Secret:**
The secret name format Azure creates:
```
AZUREAPPSERVICE_PUBLISHPROFILE_<UNIQUE_ID>
```

Your secrets:
- **Sarel-Desktop**: `AZUREAPPSERVICE_PUBLISHPROFILE_74D7D3AC927646EB9A54D254900E95ED`
- **Master**: `AZUREAPPSERVICE_PUBLISHPROFILE_364EF80149B94C6399276BDF9FE2650A`

### 3. **Deployment Process:**
```
Push to branch
  ↓
GitHub Actions triggered
  ↓
Build job: Compile Laravel + Vite
  ↓
Deploy job: Use publish profile → Deploy to Azure
  ↓
Azure App Service: Run startup.sh
  ↓
App is live!
```

---

## 📋 Updated Workflow Structure

### Build Job (Unchanged):
```yaml
- Setup PHP 8.2 with extensions
- Setup Node.js 20
- Composer install (production, optimized)
- NPM install
- Vite build
- Create Laravel directories
- Set permissions
- Make scripts executable
- Zip artifact
- Upload artifact
```

### Deploy Job (Simplified):
```yaml
- Download artifact
- Unzip artifact
- Deploy to Azure with publish-profile
  (No login, no CLI commands needed)
```

---

## 🔐 Secrets in GitHub

### Current Secrets Being Used:
✅ `AZUREAPPSERVICE_PUBLISHPROFILE_74D7D3AC927646EB9A54D254900E95ED` (sarel-desktop)
✅ `AZUREAPPSERVICE_PUBLISHPROFILE_364EF80149B94C6399276BDF9FE2650A` (master)

### Old Secrets (Can be removed):
❌ `AZUREAPPSERVICE_CLIENTID_*` (not used anymore)
❌ `AZUREAPPSERVICE_TENANTID_*` (not used anymore)
❌ `AZUREAPPSERVICE_SUBSCRIPTIONID_*` (not used anymore)

You can safely delete the old OIDC secrets from GitHub if you want to clean up.

---

## ⚙️ Configure Startup Command (One-Time)

Since we removed the Azure CLI commands that configured the startup script, you need to **set it once manually**:

### In Azure Portal:
1. Go to **App Service** → **eperolehan**
2. Click **Settings** → **Configuration**
3. Click **General settings** tab
4. Find **Startup Command** field
5. Enter: `/home/site/wwwroot/startup.sh`
6. Click **Save** at the top
7. App will restart

**This is a one-time setup!** The startup script will run automatically on every app restart from now on.

---

## 🚀 What the Startup Script Does

The `startup.sh` file (already in your project) automatically runs:

```bash
- chmod -R 775 storage bootstrap/cache
- php artisan storage:link --force
- php artisan migrate --force
- php artisan config:cache
- php artisan route:cache
- php artisan view:cache
- php artisan optimize
```

This happens **automatically on every app restart**, so you don't need post-deployment CLI commands!

---

## 📊 Deployment Flow

### Complete Flow:
```
1. Developer pushes to master or sarel-desktop
   ↓
2. GitHub Actions triggered
   ↓
3. Build Phase:
   - Install PHP dependencies (Composer)
   - Install Node dependencies (NPM)
   - Build frontend assets (Vite)
   - Create required directories
   - Set permissions
   - Package as zip
   ↓
4. Deploy Phase:
   - Authenticate with publish profile
   - Deploy zip to Azure
   ↓
5. Azure App Service:
   - Extracts deployment
   - Runs startup.sh automatically
   - Migrations, caching, optimization
   ↓
6. ✅ Application is live!
```

---

## 🔄 Managing Publish Profiles

### View Current Secrets:
Go to GitHub Repository → Settings → Secrets and variables → Actions

### Rotate/Update Publish Profile:
If you need to regenerate the publish profile:

1. **Azure Portal** → **App Service** → **eperolehan**
2. Click **Reset publish profile** (top toolbar)
3. Click **Get publish profile** to download new one
4. **GitHub** → Settings → Secrets → Edit the secret
5. Paste new publish profile XML content
6. Save

---

## ✅ Verification Checklist

After the workflow updates:

- [x] Workflows updated to use publish-profile
- [x] Removed OIDC authentication
- [x] Removed Azure CLI commands
- [ ] Set startup command in Azure Portal (do this once)
- [ ] Test deployment by pushing to sarel-desktop
- [ ] Verify app deploys successfully
- [ ] Check Azure logs to confirm startup.sh runs
- [ ] Verify artisan commands execute

---

## 🔍 Monitoring and Logs

### View Deployment Logs:
**GitHub:**
- Repository → Actions tab → Select workflow run

**Azure Portal:**
- App Service → Deployment Center → Logs

### View Application Logs:
**Azure Portal:**
- App Service → Monitoring → Log stream

**Azure CLI:**
```bash
az webapp log tail --name eperolehan --resource-group eperolehan_group
```

### View Startup Script Logs:
The startup.sh output appears in the application logs when the app starts.

---

## 🎯 Benefits of This Setup

### Simplified:
✅ Uses Azure's native GitHub integration
✅ Only publish profile authentication (1 secret per environment)
✅ No complex OIDC setup
✅ No Azure CLI dependencies in workflow

### Automated:
✅ Frontend assets built during deployment
✅ Laravel optimized for production
✅ Database migrations run automatically
✅ Caching configured on every restart

### Maintainable:
✅ Standard Azure App Service pattern
✅ Easy to troubleshoot
✅ Compatible with all App Service plans
✅ Startup script handles all Laravel setup

---

## 🛠️ Troubleshooting

### Issue: Deployment succeeds but app shows errors
**Check:**
1. Environment variables set in Azure (APP_KEY, DB_*, etc.)
2. Startup script logs in Azure Log stream
3. Storage permissions are correct

### Issue: Frontend assets not loading
**Check:**
1. `npm run build` completed in build job
2. `public/build/` directory exists in deployment
3. `.env` has correct `APP_URL`

### Issue: Migrations not running
**Check:**
1. Startup command is set in Azure Configuration
2. Database credentials are correct in Azure Configuration
3. Startup script is executable (chmod +x in build job)
4. View logs in Azure Log stream

### Issue: 401 Unauthorized during deployment
**Solution:**
1. Verify publish profile secret exists in GitHub
2. Check secret name matches workflow
3. Re-download and update publish profile if needed

---

## 📁 Important Files

### In Your Repository:
- `.github/workflows/master_eperolehan.yml` - Master branch deployment
- `.github/workflows/sarel-desktop_eperolehan.yml` - Dev branch deployment
- `startup.sh` - Runs on every app restart
- `deploy.sh` - Optional manual execution script
- `web.config` - IIS configuration (if Windows App Service)
- `.deployment` - Azure deployment configuration

### In Azure:
- **Configuration → Application settings** - Environment variables
- **Configuration → General settings → Startup Command** - Must be set to `/home/site/wwwroot/startup.sh`

---

## 📝 Next Steps

1. **Set Startup Command in Azure Portal** (one-time):
   ```
   Settings → Configuration → General settings → Startup Command
   Value: /home/site/wwwroot/startup.sh
   ```

2. **Test Deployment**:
   ```powershell
   git add .github/workflows/
   git commit -m "Update workflows to use Azure App Service GitHub integration publish profile"
   git push origin sarel-desktop
   ```

3. **Monitor the Workflow**:
   - Go to GitHub Actions tab
   - Watch the deployment run
   - Verify build and deploy succeed

4. **Verify Application**:
   - Access your Azure app URL
   - Check functionality
   - Review Azure logs

5. **Clean Up (Optional)**:
   - Remove old OIDC secrets from GitHub
   - Document the setup for your team

---

## 🎉 Summary

✅ **Both workflows now use Azure App Service GitHub integration method**
✅ **Simplified to publish profile authentication (1 secret each)**
✅ **Removed complex OIDC and CLI commands**
✅ **Automatic Laravel setup via startup.sh**
✅ **Production-ready deployment pipeline**

Your deployment setup is now aligned with Azure App Service's native GitHub integration! 🚀


