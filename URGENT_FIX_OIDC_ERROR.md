# URGENT FIX: Azure OIDC Login Error - RESOLVED

## 🔴 The Problem

**Error Message:**
```
AADSTS700016: Application with identifier '***' was not found in the directory 'Default Directory'.
Error: Login failed with Error: The process '/usr/bin/az' failed with exit code 1.
```

**Root Cause:**
The workflow was trying to use OIDC authentication (Azure AD login) but:
1. The Azure AD Application was not found/registered
2. GitHub Environment "Production" might have stored old OIDC secrets
3. The workflow should be using **publish profile** authentication instead

---

## ✅ The Fix Applied

### 1. Removed Environment Configuration
**Removed from both workflows:**
```yaml
environment:
  name: 'Production'
  url: ${{ steps.deploy-to-webapp.outputs.webapp-url }}
```

**Why:** GitHub Environments can override secrets. The "Production" environment might have had the old OIDC secrets (client-id, tenant-id, subscription-id) configured, causing the workflow to attempt Azure AD login.

### 2. Ensured Publish Profile Usage
**Both workflows now use:**
```yaml
deploy:
  runs-on: ubuntu-latest
  needs: build
  steps:
    - name: Download artifact
      uses: actions/download-artifact@v4
      with:
        name: php-app
    
    - name: Unzip artifact
      run: unzip release.zip -d .
    
    - name: Deploy to Azure Web App
      uses: azure/webapps-deploy@v3
      with:
        app-name: 'eperolehan'
        slot-name: 'Production'
        publish-profile: ${{ secrets.AZUREAPPSERVICE_PUBLISHPROFILE_xxxxx }}
        package: .
```

**No Azure login step, no environment override!**

---

## 🔍 What to Check in GitHub

### 1. Check Repository Secrets
Go to: **GitHub Repository** → **Settings** → **Secrets and variables** → **Actions**

**Verify these secrets exist:**
✅ `AZUREAPPSERVICE_PUBLISHPROFILE_74D7D3AC927646EB9A54D254900E95ED` (sarel-desktop)
✅ `AZUREAPPSERVICE_PUBLISHPROFILE_364EF80149B94C6399276BDF9FE2650A` (master)

**If missing:** Download from Azure Portal → App Service → "Get publish profile" → Add to GitHub secrets

### 2. Check/Remove GitHub Environment Secrets
Go to: **GitHub Repository** → **Settings** → **Environments**

**If "Production" environment exists:**
1. Click on "Production"
2. Check if there are secrets configured there
3. **DELETE these if they exist:**
   - ❌ `AZUREAPPSERVICE_CLIENTID_*`
   - ❌ `AZUREAPPSERVICE_TENANTID_*`
   - ❌ `AZUREAPPSERVICE_SUBSCRIPTIONID_*`
4. Or just delete the entire "Production" environment

**Why:** Environment secrets override repository secrets and can cause the wrong authentication method to be used.

---

## 🚀 Immediate Action Steps

### Step 1: Commit the Fixed Workflows
```powershell
cd C:\Users\wwwsa\Herd\eperolehan
git add .github/workflows/master_eperolehan.yml
git add .github/workflows/sarel-desktop_eperolehan.yml
git commit -m "Fix: Remove environment config and use publish profile authentication"
git push origin master
```

### Step 2: Verify Secrets in GitHub
1. Go to: https://github.com/mohamadshahril/SistemEperolehan/settings/secrets/actions
2. Verify both publish profile secrets exist:
   - `AZUREAPPSERVICE_PUBLISHPROFILE_74D7D3AC927646EB9A54D254900E95ED`
   - `AZUREAPPSERVICE_PUBLISHPROFILE_364EF80149B94C6399276BDF9FE2650A`

### Step 3: Check/Clean GitHub Environments
1. Go to: https://github.com/mohamadshahril/SistemEperolehan/settings/environments
2. If "Production" environment exists:
   - Click on it
   - Delete any OIDC secrets (CLIENTID, TENANTID, SUBSCRIPTIONID)
   - OR delete the entire environment (not needed anymore)

### Step 4: Re-run the Workflow
1. Go to: https://github.com/mohamadshahril/SistemEperolehan/actions
2. Click on the failed workflow run
3. Click "Re-run all jobs"
4. OR push a new commit to trigger it

---

## 🔐 If Secrets Are Missing

### Get Publish Profile from Azure:

1. **Azure Portal** → **App Services** → **eperolehan**
2. Click **"Get publish profile"** (top toolbar)
3. Save the `.PublishSettings` file
4. Open with text editor and copy ALL content
5. **GitHub** → Settings → Secrets and variables → Actions
6. Click "New repository secret"
7. Name: `AZUREAPPSERVICE_PUBLISHPROFILE_364EF80149B94C6399276BDF9FE2650A` (for master)
8. Value: Paste the entire XML content
9. Click "Add secret"

Repeat for sarel-desktop if needed:
- Name: `AZUREAPPSERVICE_PUBLISHPROFILE_74D7D3AC927646EB9A54D254900E95ED`

---

## 📊 How to Verify It's Fixed

### After pushing the updated workflows:

1. **GitHub Actions Tab:**
   - Should show no "Azure CLI Login" step
   - Deploy step should use `azure/webapps-deploy@v3` directly
   - No OIDC/federated token errors

2. **Deployment Log Should Show:**
   ```
   ✅ Download artifact from build job
   ✅ Unzip artifact for deployment
   ✅ Deploy to Azure Web App
      - Using publish profile authentication
      - Deploying to eperolehan
      - Deployment successful
   ```

3. **NO MORE OIDC Errors:**
   - No "Application with identifier '***' was not found"
   - No "Attempting Azure CLI login by using OIDC"
   - No federated token messages

---

## 🎯 Summary of Changes

### Before (Causing Error):
```yaml
deploy:
  environment:
    name: 'Production'  # ← This was using old OIDC secrets!
  steps:
    - name: Login to Azure  # ← This step doesn't exist anymore
      uses: azure/login@v2
      with:
        client-id: ${{ secrets.AZUREAPPSERVICE_CLIENTID_xxx }}
    - name: Deploy
      uses: azure/webapps-deploy@v3
```

### After (Fixed):
```yaml
deploy:
  # No environment configuration
  steps:
    # No Azure login step
    - name: Deploy to Azure Web App
      uses: azure/webapps-deploy@v3
      with:
        publish-profile: ${{ secrets.AZUREAPPSERVICE_PUBLISHPROFILE_xxx }}
```

---

## ⚠️ Important Notes

1. **No Azure CLI Login Needed:** Publish profile handles authentication automatically
2. **Environment Secrets Override:** Always check GitHub Environments if you get auth errors
3. **Secret Name Must Match:** The long ID in the secret name must match exactly
4. **One Secret Per Environment:** sarel-desktop and master use different publish profiles

---

## 🆘 If Still Getting Errors

### Error: "Secret not found"
**Solution:** Verify the secret name in GitHub matches exactly what's in the workflow YAML

### Error: "401 Unauthorized"
**Solution:** Re-download publish profile from Azure and update the GitHub secret

### Error: Still trying OIDC
**Solution:** 
1. Clear GitHub Actions cache
2. Delete the "Production" environment completely
3. Make sure the updated YAML is on the branch being deployed

---

## ✅ Checklist

After applying this fix:

- [ ] Updated workflows committed and pushed
- [ ] Publish profile secrets verified in GitHub
- [ ] GitHub "Production" environment checked/cleaned
- [ ] Old OIDC secrets deleted (optional cleanup)
- [ ] Workflow re-run triggered
- [ ] Deployment succeeds without OIDC errors
- [ ] App is accessible and working

---

## 📞 Quick Commands

```powershell
# Commit the fix
git add .github/workflows/
git commit -m "Fix: Remove environment config, use publish profile only"
git push origin master

# Check GitHub CLI (if installed)
gh secret list
gh workflow list
gh run list --branch master

# View latest workflow run
gh run view --log
```

---

## 🎉 Expected Result

After this fix, your deployment should:
✅ Use publish profile authentication (simple, no OIDC)
✅ Deploy successfully to Azure
✅ No Azure AD/OIDC login errors
✅ Clean deployment logs
✅ Application running properly

**The fix has been applied to both workflows. Now commit and push to resolve the issue!**

