# CI/CD Workflows Configuration Summary

## ✅ All Workflows Now Run on Master Branch

All CI/CD workflows have been updated to run on the `master` branch.

## 📋 Workflow Overview

### 1. **master_eperolehan.yml** - Azure Deployment (Production)
**Triggers:** Push to `master` branch, Manual trigger
**Purpose:** Build and deploy to Azure App Service (Production)

**Features:**
- ✅ PHP 8.2 with all required extensions
- ✅ Node.js 20 setup
- ✅ NPM install and Vite build
- ✅ Creates Laravel required directories
- ✅ Sets proper permissions
- ✅ Makes deployment scripts executable
- ✅ Zips artifact for deployment
- ✅ Deploys to Azure App Service
- ✅ Configures startup script
- ✅ Runs Laravel artisan commands automatically
- ✅ Uses OIDC authentication (secure)

**Secrets Required:**
- `AZUREAPPSERVICE_CLIENTID_364EF80149B94C6399276BDF9FE2650A`
- `AZUREAPPSERVICE_TENANTID_1B964DDEFC7B40B0BB01B76F11B9620A`
- `AZUREAPPSERVICE_SUBSCRIPTIONID_21F13B3AA36446A7B36F583ED420EC83`

---

### 2. **sarel-desktop_eperolehan.yml** - Azure Deployment (Development)
**Triggers:** Push to `sarel-desktop` branch, Manual trigger
**Purpose:** Build and deploy to Azure App Service (Development/Testing)

**Features:** Same as master workflow
**Secrets Required:**
- `AZUREAPPSERVICE_CLIENTID_74D7D3AC927646EB9A54D254900E95ED`
- `AZUREAPPSERVICE_TENANTID_35BD0BFD3D85411D936A6244E8DA4EE2`
- `AZUREAPPSERVICE_SUBSCRIPTIONID_FCC1FA2B079E415AA3DA9A1480F84E52`

---

### 3. **tests.yml** - Automated Tests
**Triggers:** 
- Push to: `master`, `develop`, `main`
- Pull requests to: `master`, `develop`, `main`

**Purpose:** Run PHPUnit tests

**Features:**
- ✅ PHP 8.4 setup
- ✅ Composer dependencies install
- ✅ Node.js 22 setup
- ✅ NPM dependencies install
- ✅ Database setup (SQLite)
- ✅ Runs test suite
- ✅ Generates coverage report

---

### 4. **lint.yml** - Code Quality Linter
**Triggers:** 
- Push to: `master`, `develop`, `main`
- Pull requests to: `master`, `develop`, `main`

**Purpose:** Check code quality and style

**Features:**
- ✅ PHP 8.4 setup
- ✅ Composer and NPM dependencies
- ✅ Runs PHP CS Fixer
- ✅ Runs ESLint for JavaScript/Vue
- ✅ Auto-commits fixes (write permissions)

---

### 5. **browser-tests.yml** - Browser/E2E Tests
**Triggers:** 
- Push to: `master`, `develop`, `main`
- Pull requests to: `master`, `develop`, `main`

**Purpose:** Run browser-based end-to-end tests

**Features:**
- ✅ PHP 8.4 setup
- ✅ Node.js 22 setup
- ✅ Composer and NPM dependencies
- ✅ Chrome/Chromium setup for Dusk
- ✅ Runs browser tests
- ✅ Uploads screenshots on failure

---

## 🔄 Branch Strategy

### Master Branch (Production)
```
master
  ├── Azure Deployment (master_eperolehan.yml) ✅
  ├── Tests (tests.yml) ✅
  ├── Linter (lint.yml) ✅
  └── Browser Tests (browser-tests.yml) ✅
```

### Sarel-Desktop Branch (Development)
```
sarel-desktop
  └── Azure Deployment (sarel-desktop_eperolehan.yml) ✅
```

### Develop Branch (Development)
```
develop
  ├── Tests (tests.yml) ✅
  ├── Linter (lint.yml) ✅
  └── Browser Tests (browser-tests.yml) ✅
```

### Main Branch (Alternative)
```
main
  ├── Tests (tests.yml) ✅
  ├── Linter (lint.yml) ✅
  └── Browser Tests (browser-tests.yml) ✅
```

---

## 🚀 Deployment Flow

### For Master Branch (Production):
1. **Push to master** → Triggers all workflows
2. **Tests run** (tests.yml) → Validates code
3. **Linter runs** (lint.yml) → Checks code quality
4. **Browser tests run** (browser-tests.yml) → E2E validation
5. **Azure deployment starts** (master_eperolehan.yml)
   - Builds application
   - Compiles assets
   - Creates artifact
   - Deploys to Azure
   - Configures startup script
   - Runs artisan commands
6. **Application is live** on Azure

### For Sarel-Desktop Branch (Development):
1. **Push to sarel-desktop** → Triggers deployment workflow
2. **Azure deployment starts** (sarel-desktop_eperolehan.yml)
3. **Application deploys** to development environment

---

## 📊 CI/CD Pipeline Summary

| Workflow | Master | Sarel-Desktop | Develop | Main | PR |
|----------|--------|---------------|---------|------|-----|
| Azure Deploy (Production) | ✅ | ❌ | ❌ | ❌ | ❌ |
| Azure Deploy (Dev) | ❌ | ✅ | ❌ | ❌ | ❌ |
| Tests | ✅ | ❌ | ✅ | ✅ | ✅ |
| Linter | ✅ | ❌ | ✅ | ✅ | ✅ |
| Browser Tests | ✅ | ❌ | ✅ | ✅ | ✅ |

---

## 🛠️ Manual Triggers

All workflows can be manually triggered via GitHub Actions UI:
1. Go to **GitHub Repository** → **Actions** tab
2. Select the workflow you want to run
3. Click **Run workflow** button
4. Select branch and click **Run workflow**

---

## 📝 Environment Variables Required in Azure

For both master and sarel-desktop deployments, ensure these are set in Azure App Service Configuration:

### Required:
- `APP_KEY` - Laravel application key
- `APP_ENV` - `production`
- `APP_DEBUG` - `false`
- `APP_URL` - Your Azure app URL

### Database:
- `DB_CONNECTION` - `mysql` or your database type
- `DB_HOST` - Database host
- `DB_PORT` - Database port (usually 3306)
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password

### Optional:
- `MAIL_*` - Mail configuration
- `CACHE_DRIVER` - Cache driver (redis/file)
- `SESSION_DRIVER` - Session driver
- `QUEUE_CONNECTION` - Queue connection

---

## ✅ Verification Checklist

After pushing to master, verify:
- [ ] Tests workflow runs and passes
- [ ] Linter workflow runs and passes
- [ ] Browser tests workflow runs and passes
- [ ] Azure deployment workflow starts
- [ ] Build completes successfully
- [ ] Assets are compiled (check public/build)
- [ ] Deployment to Azure succeeds
- [ ] Startup script is configured
- [ ] Artisan commands run successfully
- [ ] Application is accessible on Azure
- [ ] No errors in Azure logs

---

## 🔍 Monitoring Workflows

### View Workflow Runs:
```bash
# List recent workflow runs
gh run list

# Watch a specific run
gh run watch

# View logs for a run
gh run view <run-id> --log
```

### View Azure Logs:
```bash
# Real-time logs
az webapp log tail --name eperolehan --resource-group eperolehan_group

# Download logs
az webapp log download --name eperolehan --resource-group eperolehan_group --log-file logs.zip
```

---

## 🎯 Best Practices

1. **Always test in sarel-desktop first** before merging to master
2. **Monitor workflow runs** on GitHub Actions tab
3. **Check Azure logs** after deployment
4. **Use pull requests** for code review before merging to master
5. **Keep secrets secure** - never commit them to the repository
6. **Tag releases** after successful master deployments
7. **Rollback plan** - keep previous deployment artifacts

---

## 📚 Related Documentation

- `AZURE_DEPLOYMENT_FIX.md` - Complete Azure deployment guide
- `AZURE_ARTISAN_AUTOMATION.md` - Laravel artisan automation guide
- `AZURE_STARTUP_COMMAND_GUIDE.md` - Startup command configuration
- GitHub Actions Docs: https://docs.github.com/en/actions
- Azure Web Apps Deploy: https://github.com/Azure/webapps-deploy

---

## 🎉 Summary

✅ **All CI/CD workflows are now configured to run on the master branch!**

- Master branch triggers production deployment + all tests
- Sarel-desktop branch triggers development deployment
- All quality checks (tests, linter, browser tests) run on master
- Automated deployment with Laravel artisan commands
- Secure OIDC authentication for Azure
- Complete observability with logs and monitoring

Your CI/CD pipeline is production-ready! 🚀

