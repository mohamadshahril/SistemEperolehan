# Switch to Publish Profile Authentication - Step-by-Step Guide

## 🎯 Goal
Replace OIDC authentication (3 secrets) with Publish Profile (1 secret) for simpler deployment.

---

## 📋 Step-by-Step Instructions

### Step 1: Download Publish Profile from Azure

1. **Open Azure Portal**: https://portal.azure.com
2. **Navigate to**: App Services → **eperolehan**
3. **Click**: "Get publish profile" button (top toolbar, looks like a download icon)
4. **Save**: The downloaded file (e.g., `eperolehan.PublishSettings`)

**What the file looks like:**
```xml
<publishData>
  <publishProfile profileName="eperolehan - Web Deploy" 
    publishMethod="MSDeploy" 
    publishUrl="eperolehan.scm.azurewebsites.net:443" 
    msdeploySite="eperolehan" 
    userName="$eperolehan" 
    userPWD="..." 
    ...>
  </publishProfile>
</publishData>
```

---

### Step 2: Add Publish Profile to GitHub Secrets

1. **Open** the downloaded `.PublishSettings` file with a text editor (Notepad, VSCode, etc.)
2. **Copy** the entire XML content (Ctrl+A, Ctrl+C)
3. **Go to GitHub**: Your repository → **Settings** tab
4. **Navigate**: Secrets and variables → **Actions**
5. **Click**: "New repository secret" button
6. **Name**: `AZURE_WEBAPP_PUBLISH_PROFILE`
7. **Value**: Paste the entire XML content
8. **Click**: "Add secret"

**Screenshot path in GitHub:**
```
Repository → Settings → Secrets and variables → Actions → New repository secret
```

---

### Step 3: Update Your Workflow File

**Choose one of these options:**

#### Option A: Replace Current Workflow (Recommended for Development)

Update `sarel-desktop_eperolehan.yml`:

**Remove these lines:**
```yaml
- name: Login to Azure
  uses: azure/login@v2
  with:
    client-id: ${{ secrets.AZUREAPPSERVICE_CLIENTID_74D7D3AC927646EB9A54D254900E95ED }}
    tenant-id: ${{ secrets.AZUREAPPSERVICE_TENANTID_35BD0BFD3D85411D936A6244E8DA4EE2 }}
    subscription-id: ${{ secrets.AZUREAPPSERVICE_SUBSCRIPTIONID_FCC1FA2B079E415AA3DA9A1480F84E52 }}

- name: 'Configure Startup Script'
  run: |
    az webapp config set --name eperolehan --resource-group eperolehan_group --startup-file "/home/site/wwwroot/startup.sh" || echo "Startup script configuration may not be supported on this plan"

- name: 'Run Post-Deployment Commands'
  run: |
    echo "Running post-deployment setup..."
    az webapp ssh --name eperolehan --resource-group eperolehan_group --command "cd /home/site/wwwroot && bash deploy.sh" || \
    az webapp ssh --name eperolehan --resource-group eperolehan_group --command "cd /home/site/wwwroot && php artisan migrate --force && php artisan storage:link && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize" || \
    echo "SSH commands may not be available. Please run artisan commands manually in Azure console."
```

**Replace the Deploy step with:**
```yaml
- name: 'Deploy to Azure Web App'
  uses: azure/webapps-deploy@v3
  id: deploy-to-webapp
  with:
    app-name: 'eperolehan'
    slot-name: 'Production'
    publish-profile: ${{ secrets.AZURE_WEBAPP_PUBLISH_PROFILE }}
    package: .
```

**Also remove this from the deploy job:**
```yaml
permissions:
  id-token: write #This is required for requesting the JWT
  contents: read #This is required for actions/checkout
```

---

#### Option B: Use the Pre-Made Workflow File

1. **Rename or disable** current workflow:
   ```powershell
   # Rename to backup
   mv .github/workflows/sarel-desktop_eperolehan.yml .github/workflows/sarel-desktop_eperolehan_oidc.yml.bak
   ```

2. **Rename the new workflow**:
   ```powershell
   # Activate publish profile version
   mv .github/workflows/sarel-desktop_eperolehan_publish-profile.yml .github/workflows/sarel-desktop_eperolehan.yml
   ```

---

### Step 4: Configure Startup Command in Azure (One-Time)

Since publish profile doesn't support Azure CLI commands, set the startup command manually:

1. **Azure Portal** → **App Services** → **eperolehan**
2. **Settings** → **Configuration**
3. **General settings** tab
4. **Startup Command** field: `/home/site/wwwroot/startup.sh`
5. **Click**: Save
6. **Confirm**: App will restart

**This is a one-time setup!** The startup script will run automatically on every app restart.

---

### Step 5: Test the Deployment

1. **Commit and push** your changes:
   ```powershell
   git add .github/workflows/sarel-desktop_eperolehan.yml
   git commit -m "Switch to publish profile authentication for simpler deployment"
   git push origin sarel-desktop
   ```

2. **Monitor** the GitHub Actions workflow run
3. **Verify** deployment succeeds
4. **Check** Azure logs to confirm startup script runs

---

## 🔄 Full Updated Workflow (Publish Profile Version)

```yaml
deploy:
  runs-on: ubuntu-latest
  needs: build
  environment:
    name: 'Production'
    url: ${{ steps.deploy-to-webapp.outputs.webapp-url }}

  steps:
    - name: Download artifact from build job
      uses: actions/download-artifact@v4
      with:
        name: php-app

    - name: Unzip artifact for deployment
      run: unzip release.zip -d .

    # NO AZURE LOGIN STEP NEEDED!

    - name: 'Deploy to Azure Web App'
      uses: azure/webapps-deploy@v3
      id: deploy-to-webapp
      with:
        app-name: 'eperolehan'
        slot-name: 'Production'
        publish-profile: ${{ secrets.AZURE_WEBAPP_PUBLISH_PROFILE }}
        package: .
```

---

## ✅ Verification Checklist

After switching to publish profile:

- [ ] Downloaded publish profile from Azure Portal
- [ ] Added `AZURE_WEBAPP_PUBLISH_PROFILE` secret to GitHub
- [ ] Updated workflow to use `publish-profile` parameter
- [ ] Removed Azure CLI login step
- [ ] Removed post-deployment CLI commands
- [ ] Set startup command in Azure Portal manually
- [ ] Committed and pushed changes
- [ ] Workflow runs successfully
- [ ] Deployment completes
- [ ] App is accessible
- [ ] Startup script runs on restart

---

## 🎯 What Happens Now

### On Every Push to sarel-desktop:
1. ✅ Build job runs (PHP, Node, Vite, create directories, etc.)
2. ✅ Artifact is created and zipped
3. ✅ Deploy job downloads artifact
4. ✅ Deploys to Azure using publish profile (simple authentication)
5. ✅ Azure App Service restarts
6. ✅ `startup.sh` runs automatically (migrations, caching, optimization)
7. ✅ App is ready!

---

## 🔐 Security Comparison

### Before (OIDC):
- 3 secrets to manage
- Token-based (most secure)
- Requires Azure AD setup
- Supports Azure CLI commands

### After (Publish Profile):
- 1 secret to manage
- Username/password based (basic security)
- Easy to download and setup
- No Azure CLI command support

---

## 🔄 Rotating Publish Profile

Publish profiles should be rotated periodically for security:

1. **Azure Portal** → **App Service** → **eperolehan**
2. **Click**: "Reset publish profile" (top toolbar)
3. **Download** new publish profile
4. **Update** GitHub secret with new content

---

## 🆘 Troubleshooting

### Issue: Deployment fails with 401 Unauthorized
**Solution**: 
- Verify publish profile secret is correct
- Download a fresh publish profile from Azure
- Update the GitHub secret

### Issue: Old secrets still exist
**Solution**:
You can keep or delete the old OIDC secrets:
- `AZUREAPPSERVICE_CLIENTID_*`
- `AZUREAPPSERVICE_TENANTID_*`
- `AZUREAPPSERVICE_SUBSCRIPTIONID_*`

They won't be used anymore but won't cause issues if left.

### Issue: Startup commands don't run
**Solution**:
- Verify startup command is set in Azure Portal Configuration
- Check Azure logs: `az webapp log tail --name eperolehan --resource-group eperolehan_group`
- Verify startup.sh is executable and deployed

---

## 💡 Pro Tips

1. **For master branch**: Keep OIDC (more secure for production)
2. **For sarel-desktop**: Use publish profile (simpler for dev/testing)
3. **Set startup command once**: It persists across deployments
4. **Monitor first deployment**: Ensure startup script runs correctly
5. **Keep publish profile secret**: Don't share or commit it

---

## 📞 Quick Commands

### Download logs:
```powershell
az webapp log download --name eperolehan --resource-group eperolehan_group --log-file logs.zip
```

### View real-time logs:
```powershell
az webapp log tail --name eperolehan --resource-group eperolehan_group
```

### Manually trigger startup script (if needed):
Go to Azure Console (SSH) and run:
```bash
cd /home/site/wwwroot
bash startup.sh
```

---

## ✨ Summary

**Publish Profile Method:**
- ✅ Simpler: Only 1 secret needed
- ✅ Easier: Direct download from Azure
- ✅ Faster: Less configuration required
- ⚠️ Less secure: Long-lived credentials
- ⚠️ Manual: Startup command set once in Azure Portal
- ❌ No CLI: Can't run Azure CLI commands post-deployment

**Perfect for development/testing environments!**


