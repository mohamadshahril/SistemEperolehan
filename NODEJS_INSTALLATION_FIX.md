# Node.js Installation Fix Guide

## Problem
The error "Could not determine Node.js install directory" occurs because Node.js is either:
1. Not installed on your system
2. Not properly added to your system PATH

## Diagnosis
Running `node --version` shows: "node is not recognized as the name of a cmdlet"
This confirms Node.js is not accessible from the command line.

## Solution

### Option 1: Install Node.js (Recommended)

1. **Download Node.js**
   - Visit: https://nodejs.org/
   - Download the **LTS (Long Term Support)** version for Windows
   - Recommended: Node.js 20.x or 22.x LTS

2. **Install Node.js**
   - Run the downloaded installer (.msi file)
   - Follow the installation wizard
   - **IMPORTANT**: Make sure to check the box that says "Automatically install the necessary tools"
   - Accept the default installation path (usually `C:\Program Files\nodejs\`)
   - The installer will automatically add Node.js to your PATH

3. **Verify Installation**
   - Close and reopen your terminal/PowerShell
   - Run: `node --version`
   - Run: `npm --version`
   - Both should display version numbers

4. **Install Project Dependencies**
   ```bash
   npm install
   ```

5. **Run Development Server**
   ```bash
   npm run dev
   ```

### Option 2: Use Laravel Herd (Alternative for Laravel Projects)

Since you're working with a Laravel project, you might be using Laravel Herd which includes Node.js:

1. **Check if Herd is installed**
   - Look for Herd in your system tray or applications
   - Herd includes Node.js, PHP, and other tools

2. **If using Herd, restart it**
   - Right-click Herd icon in system tray
   - Select "Restart"
   - This should refresh the PATH variables

3. **Open a new terminal from Herd**
   - Herd provides its own terminal with proper PATH setup
   - Try running `npm run dev` from there

### Option 3: Use NVM (Node Version Manager) for Windows

If you need to manage multiple Node.js versions:

1. **Download NVM for Windows**
   - Visit: https://github.com/coreybutler/nvm-windows/releases
   - Download `nvm-setup.exe`

2. **Install NVM**
   - Run the installer
   - Follow the installation wizard

3. **Install Node.js via NVM**
   ```bash
   nvm install lts
   nvm use lts
   ```

4. **Verify Installation**
   ```bash
   node --version
   npm --version
   ```

## After Installing Node.js

1. **Install Project Dependencies**
   ```bash
   npm install
   ```
   This will install all packages listed in package.json

2. **Run Development Server**
   ```bash
   npm run dev
   ```
   This will start Vite development server for your Laravel + Vue.js project

3. **Expected Output**
   You should see something like:
   ```
   VITE v7.0.4  ready in XXX ms

   ➜  Local:   http://localhost:5173/
   ➜  Network: use --host to expose
   ➜  press h + enter to show help
   ```

## Troubleshooting

### If Node.js is installed but still not recognized:

1. **Check PATH Environment Variable**
   - Press `Win + X` and select "System"
   - Click "Advanced system settings"
   - Click "Environment Variables"
   - Under "System variables", find "Path"
   - Verify these paths exist:
     - `C:\Program Files\nodejs\`
     - `%APPDATA%\npm`

2. **Restart Your Computer**
   - Sometimes PATH changes require a full system restart

3. **Use Full Path Temporarily**
   ```bash
   "C:\Program Files\nodejs\npm.exe" run dev
   ```

### If npm install fails:

1. **Clear npm cache**
   ```bash
   npm cache clean --force
   ```

2. **Delete node_modules and package-lock.json**
   ```bash
   rmdir /s /q node_modules
   del package-lock.json
   npm install
   ```

3. **Run as Administrator**
   - Right-click PowerShell/CMD
   - Select "Run as Administrator"
   - Navigate to project directory
   - Run `npm install`

## Project-Specific Information

Your project uses:
- **Vite 7.0.4** - Modern build tool
- **Vue 3.5.13** - Frontend framework
- **Inertia.js** - Laravel + Vue.js integration
- **TypeScript** - Type-safe JavaScript
- **Tailwind CSS 4.x** - Utility-first CSS framework

The `npm run dev` command starts the Vite development server which:
- Compiles Vue components
- Processes TypeScript files
- Handles hot module replacement (HMR)
- Serves assets for your Laravel application

## Next Steps After Fix

1. Start the development server: `npm run dev`
2. In a separate terminal, start Laravel: `php artisan serve` or use Herd
3. Access your application at: `http://localhost` (if using Herd) or `http://localhost:8000`

## Additional Commands

- `npm run build` - Build for production
- `npm run build:ssr` - Build with SSR support
- `npm run format` - Format code with Prettier
- `npm run lint` - Lint code with ESLint

## Support

If you continue to experience issues after following this guide, please provide:
1. Output of `node --version` (after installation)
2. Output of `npm --version` (after installation)
3. Output of `npm install` (if it fails)
4. Any error messages you receive
