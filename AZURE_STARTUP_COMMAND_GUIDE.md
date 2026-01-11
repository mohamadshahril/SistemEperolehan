# Azure App Service Startup Command Configuration

## 🎯 Startup Command Path for Azure

For your eperolehan app, use this **absolute path**:

```
/home/site/wwwroot/startup.sh
```

## 📝 How to Configure (Multiple Methods)

### Method 1: Via GitHub Actions (Automated) ✅ RECOMMENDED
The workflow now automatically sets this on every deployment:
```yaml
az webapp config set --name eperolehan --resource-group eperolehan_group --startup-file "/home/site/wwwroot/startup.sh"
```

### Method 2: Via Azure Portal (Manual)
1. Go to **Azure Portal** (https://portal.azure.com)
2. Navigate to your App Service: **eperolehan**
3. In the left menu, go to **Settings** → **Configuration**
4. Click on the **General settings** tab
5. Find **Startup Command** field
6. Enter: `/home/site/wwwroot/startup.sh`
7. Click **Save** at the top
8. Wait for the app to restart

### Method 3: Via Azure CLI (Manual)
```bash
az webapp config set --name eperolehan --resource-group eperolehan_group --startup-file "/home/site/wwwroot/startup.sh"
```

### Method 4: Via Azure Cloud Shell
1. Go to Azure Portal
2. Click on the **Cloud Shell** icon (>_) at the top
3. Run:
```bash
az webapp config set --name eperolehan --resource-group eperolehan_group --startup-file "/home/site/wwwroot/startup.sh"
```

## 🔍 Verify Startup Command is Set

### Via Azure CLI:
```bash
az webapp config show --name eperolehan --resource-group eperolehan_group --query appCommandLine
```

Expected output:
```
"/home/site/wwwroot/startup.sh"
```

### Via Azure Portal:
1. Go to App Service → Configuration → General settings
2. Check the **Startup Command** field

## 📂 Important Path Information

### For Azure App Service (Linux):
- **App Root**: `/home/site/wwwroot`
- **Startup Script**: `/home/site/wwwroot/startup.sh`
- **Deploy Script**: `/home/site/wwwroot/deploy.sh`
- **Laravel Artisan**: `/home/site/wwwroot/artisan`
- **Public Directory**: `/home/site/wwwroot/public`
- **Storage**: `/home/site/wwwroot/storage`

### For Azure App Service (Windows):
- **App Root**: `D:\home\site\wwwroot`
- Startup commands work differently (use web.config instead)
- The `web.config` file we created handles this

## 🔧 Alternative Startup Commands

If you need different startup behavior:

### Option 1: Run startup.sh with bash explicitly
```
/bin/bash /home/site/wwwroot/startup.sh
```

### Option 2: Run PHP artisan commands directly
```
php /home/site/wwwroot/artisan migrate --force && php /home/site/wwwroot/artisan config:cache
```

### Option 3: Custom startup with logging
```
/bin/bash /home/site/wwwroot/startup.sh > /home/LogFiles/startup.log 2>&1
```

## 📊 What Happens with Startup Command

### When App Service Starts:
```
1. Container starts
2. Azure runs: /home/site/wwwroot/startup.sh
3. Script executes:
   - chmod permissions
   - php artisan storage:link
   - php artisan migrate --force
   - php artisan config:cache
   - php artisan route:cache
   - php artisan view:cache
   - php artisan optimize
4. App begins serving traffic
```

## ⚠️ Troubleshooting Startup Command

### Check if startup command is running:
```bash
# View startup logs
az webapp log tail --name eperolehan --resource-group eperolehan_group

# Or download logs
az webapp log download --name eperolehan --resource-group eperolehan_group --log-file logs.zip
```

### Common Issues:

#### Issue 1: Script not executable
**Solution:**
```bash
# In Azure Console/SSH
cd /home/site/wwwroot
chmod +x startup.sh
```
The workflow now handles this automatically during build.

#### Issue 2: Script not found
**Solution:**
- Verify the file was deployed: Check in Azure Console
- Ensure the path is absolute: `/home/site/wwwroot/startup.sh`
- Check if file exists:
```bash
az webapp ssh --name eperolehan --resource-group eperolehan_group --command "ls -la /home/site/wwwroot/startup.sh"
```

#### Issue 3: Script has Windows line endings (CRLF)
**Solution:**
```bash
# In Azure Console/SSH
cd /home/site/wwwroot
dos2unix startup.sh
# Or
sed -i 's/\r$//' startup.sh
```

#### Issue 4: Startup command timeout
**Solution:**
- Startup commands have a timeout (usually 230 seconds)
- If migrations take too long, run them via deploy.sh instead
- Consider using separate migration job

## 🧪 Test Startup Script

### Test in Azure SSH Console:
```bash
# Connect to SSH
az webapp ssh --name eperolehan --resource-group eperolehan_group

# Navigate to app root
cd /home/site/wwwroot

# Make executable
chmod +x startup.sh

# Test run
bash startup.sh

# Check output
echo $?
```

### Test locally with Docker (simulate Azure environment):
```bash
docker run -it --rm -v ${PWD}:/app php:8.2-cli bash
cd /app
bash startup.sh
```

## 📱 View Logs

### Real-time logs:
```bash
az webapp log tail --name eperolehan --resource-group eperolehan_group
```

### Application logs in Azure Portal:
1. App Service → Monitoring → **Log stream**
2. App Service → Monitoring → **App Service logs**
   - Enable: **Application Logging (Filesystem)**
   - Level: **Information**

### Download logs:
```bash
az webapp log download --name eperolehan --resource-group eperolehan_group --log-file logs.zip
```

## 🎯 Current Configuration Summary

### Your eperolehan App Service:
- **App Name**: `eperolehan`
- **Resource Group**: `eperolehan_group`
- **Startup Command**: `/home/site/wwwroot/startup.sh`
- **Deployment Method**: GitHub Actions (automated)
- **PHP Version**: 8.2
- **Framework**: Laravel with Inertia.js

### Automatic Execution:
✅ **On Deployment**: `deploy.sh` runs via GitHub Actions SSH
✅ **On Every Restart**: `startup.sh` runs via Azure startup command
✅ **Both scripts run**: migrations, caching, optimization

## 🔄 Update Startup Command Anytime

If you need to change the startup command later:

```bash
# Set new startup command
az webapp config set --name eperolehan --resource-group eperolehan_group --startup-file "/path/to/new/script.sh"

# Clear startup command (use default)
az webapp config set --name eperolehan --resource-group eperolehan_group --startup-file ""

# Restart app to apply
az webapp restart --name eperolehan --resource-group eperolehan_group
```

## ✅ Verification Checklist

After deployment, verify:
- [ ] Startup command is set in Azure Portal → Configuration
- [ ] startup.sh file exists in `/home/site/wwwroot/`
- [ ] startup.sh is executable (chmod +x)
- [ ] Logs show startup.sh execution
- [ ] Database migrations completed
- [ ] Storage link created
- [ ] Caches are warmed
- [ ] App is serving traffic

## 🆘 If Startup Script Fails

The app will still start, but Laravel commands won't run automatically.

**Fallback options:**
1. Run commands manually in Azure Console
2. Use the deploy.sh script via SSH
3. Create a separate Azure deployment slot for testing
4. Use Azure WebJobs for long-running tasks

## 📞 Quick Commands Reference

```bash
# Check current startup command
az webapp config show --name eperolehan --resource-group eperolehan_group --query appCommandLine

# Set startup command
az webapp config set --name eperolehan --resource-group eperolehan_group --startup-file "/home/site/wwwroot/startup.sh"

# View logs
az webapp log tail --name eperolehan --resource-group eperolehan_group

# SSH into app
az webapp ssh --name eperolehan --resource-group eperolehan_group

# Restart app
az webapp restart --name eperolehan --resource-group eperolehan_group

# Test startup script
az webapp ssh --name eperolehan --resource-group eperolehan_group --command "bash /home/site/wwwroot/startup.sh"
```

---

**Note**: The GitHub Actions workflow now automatically configures this startup command on every deployment, so you don't need to set it manually! 🚀

