# 🧪 FC Chiché - Phase 8: Testing & Verification Guide

**Date:** 2025-11-08
**Purpose:** Complete testing of the React migration, backend connectivity verification, and end-to-end validation
**Duration:** 4-6 hours estimated
**Status:** Ready to execute

---

## 📋 Table of Contents

1. [Quick Start (5 minutes)](#1-quick-start)
2. [Backend Setup & Verification](#2-backend-setup--verification)
3. [Frontend Development Setup](#3-frontend-development-setup)
4. [API Integration Testing](#4-api-integration-testing)
5. [Component & Page Testing](#5-component--page-testing)
6. [Performance & Quality Audits](#6-performance--quality-audits)
7. [Cross-Browser Testing](#7-cross-browser-testing)
8. [Troubleshooting](#8-troubleshooting)
9. [Test Results Checklist](#9-test-results-checklist)

---

## 1. QUICK START

### 1.1 Prerequisites Check

```bash
# Terminal 1: Check Node.js version
node --version
# Expected: v18+ or v20+

npm --version
# Expected: v9+
```

### 1.2 Environment Setup

```bash
# Navigate to React project
cd C:\Développement\fcchiche-react

# Install dependencies (first time only)
npm install

# Check installation
npm list react react-dom react-router-dom
```

### 1.3 Start Development Servers (3 minutes)

**Terminal 1 - Start React Frontend:**

```bash
cd C:\Développement\fcchiche-react
npm run dev
# Expected output:
# ▶ Local: http://localhost:5173/
# ▶ Pressing 'o' opens the URL in your default browser
```

**Terminal 2 - Start PHP Backend (if needed locally):**

```bash
# If you have PHP installed locally
cd C:\Développement\fcchiche
php -S localhost:8000 -t public
# Expected output:
# Development Server (http://localhost:8000) started
```

Or if using XAMPP/WAMP/MAMP:
```bash
# Start your local PHP server (Apache/Nginx)
# Usually: http://localhost or http://localhost:8080
```

---

## 2. BACKEND SETUP & VERIFICATION

### 2.1 Backend Architecture Check

The backend is located at: `C:\Développement\fcchiche\public\api\`

**API Endpoints Available:**

```
✅ GET    /api/club              - Club information
✅ GET    /api/classements       - League standings
✅ GET    /api/competitions      - Competitions list
✅ GET    /api/config            - System configuration
✅ GET    /api/engagements       - Team engagements
✅ GET    /api/equipes           - Teams list
✅ GET    /api/matchs            - All matches
✅ GET    /api/membres           - Members list
✅ GET    /api/terrains          - Pitches/fields
✅ GET    /api/sync-logs         - Synchronization logs
✅ POST   /api/auth              - Login (JWT)
✅ POST   /api/matchs            - Create match
✅ PUT    /api/matchs/:id        - Update match
✅ DELETE /api/matchs/:id        - Delete match
```

### 2.2 Backend File Structure

```
fcchiche/
├── public/api/
│   ├── auth.php              → Authentication endpoint
│   ├── club.php              → Club info
│   ├── classements.php       → League standings
│   ├── competitions.php      → Competitions
│   ├── config.php            → Configuration
│   ├── docs.html             → API documentation
│   ├── engagements.php       → Team engagements
│   ├── equipes.php           → Teams list
│   ├── matchs.php            → Matches CRUD
│   ├── membres.php           → Members
│   ├── sync-logs.php         → Sync logs
│   ├── terrains.php          → Fields
│   ├── index.php             → Router
│   └── openapi.yaml          → OpenAPI schema
├── src/
│   ├── Models/               → Database models
│   ├── Utils/                → Utilities (ApiResponse, ApiAuth)
│   └── API/                  → API classes
├── config/
│   ├── bootstrap.php         → Application bootstrap
│   └── database.php          → Database configuration
└── sql/
    └── structure.sql         → Database schema
```

### 2.3 Backend Verification Steps

#### Step 1: Check Database Connection

```bash
# Option A: If you have access to MySQL directly
mysql -h localhost -u root -p fcchiche

# Option B: Via PHP connection test
php -r "
\$pdo = new PDO('mysql:host=localhost;dbname=fcchiche', 'root', '');
echo 'Database connection successful!';
"
```

#### Step 2: Test Backend Routes

**If running locally (http://localhost:8000):**

```bash
# Test basic endpoint
curl -s "http://localhost:8000/api/config" | jq .

# Test with auth endpoint
curl -X POST "http://localhost:8000/api/auth?action=login" \
  -H "Content-Type: application/json" \
  -d '{"username":"Administrateur","password":"your_password"}' | jq .
```

**If connecting to preprod (https://preprod.fcchiche.fr):**

```bash
# Test preprod endpoint
curl -s "https://preprod.fcchiche.fr/api/config" | jq .
```

#### Step 3: Check CORS Headers

```bash
# Test CORS preflight request
curl -i -X OPTIONS "http://localhost:8000/api/matchs" \
  -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: GET"

# Expected headers:
# Access-Control-Allow-Origin: *
# Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
# Access-Control-Allow-Headers: Content-Type, Authorization
```

---

## 3. FRONTEND DEVELOPMENT SETUP

### 3.1 React Development Server

**Start the development server:**

```bash
cd C:\Développement\fcchiche-react

# Clear any previous build
npm run clean 2>/dev/null || rm -rf dist node_modules

# Install fresh dependencies
npm install

# Start dev server with hot module replacement
npm run dev
```

**Expected Output:**

```
  VITE v5.x.x  ready in xxxms

  ➜  Local:   http://localhost:5173/
  ➜  press h + enter to show help
```

### 3.2 Browser Access

Open: **http://localhost:5173/**

You should see:
- ✅ FC Chiché logo/header
- ✅ Navigation menu (Home, Matchs, Résultats, Classements)
- ✅ Home page content
- ✅ No red errors in console

### 3.3 Environment Configuration

The React app reads API endpoint from `.env` file:

**Local Development (`.env`):**

```bash
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=FC Chiché
VITE_APP_VERSION=1.0.0
```

To switch to **Preprod backend**, create `.env.preprod`:

```bash
VITE_API_BASE_URL=https://preprod.fcchiche.fr/api
VITE_APP_NAME=FC Chiché
VITE_APP_VERSION=1.0.0
```

Then build with:

```bash
npm run build -- --mode preprod
```

---

## 4. API INTEGRATION TESTING

### 4.1 Test Each Endpoint

#### Test 1: Public Endpoints (No Authentication)

**Terminal command:**

```bash
# Test 1a: Get club info
curl -s "http://localhost:8000/api/club" -H "Content-Type: application/json" | jq . | head -20

# Test 1b: Get competitions
curl -s "http://localhost:8000/api/competitions" -H "Content-Type: application/json" | jq . | head -20

# Test 1c: Get league standings
curl -s "http://localhost:8000/api/classements" -H "Content-Type: application/json" | jq . | head -20

# Test 1d: Get matchs
curl -s "http://localhost:8000/api/matchs" -H "Content-Type: application/json" | jq . | head -30

# Test 1e: Get equipes
curl -s "http://localhost:8000/api/equipes" -H "Content-Type: application/json" | jq . | head -20
```

**Expected Response Format:**

```json
{
  "success": true,
  "data": {
    // endpoint-specific data
  },
  "message": "Data retrieved successfully"
}
```

#### Test 2: Authentication (JWT)

**Step 1: Login to get token**

```bash
curl -X POST "http://localhost:8000/api/auth?action=login" \
  -H "Content-Type: application/json" \
  -d '{"username":"Administrateur","password":"your_password"}' | jq .
```

**Expected Response:**

```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "name": "Administrateur",
    "email": "admin@fcchiche.fr"
  }
}
```

**Step 2: Use token in subsequent requests**

```bash
TOKEN="your_token_from_above"

curl -s "http://localhost:8000/api/config" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq .
```

#### Test 3: CRUD Operations (Matches)

**Create a match:**

```bash
TOKEN="your_token"

curl -X POST "http://localhost:8000/api/matchs" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "id_equipe_domicile": 1,
    "id_equipe_exterieur": 2,
    "date_match": "2025-11-15",
    "heure_match": "14:00",
    "id_terrain": 1,
    "score_home": null,
    "score_away": null
  }' | jq .
```

**Read a specific match:**

```bash
curl -s "http://localhost:8000/api/matchs/1" \
  -H "Content-Type: application/json" | jq .
```

**Update a match:**

```bash
TOKEN="your_token"

curl -X PUT "http://localhost:8000/api/matchs/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"score_home": 2, "score_away": 1}' | jq .
```

**Delete a match:**

```bash
TOKEN="your_token"

curl -X DELETE "http://localhost:8000/api/matchs/1" \
  -H "Authorization: Bearer $TOKEN" | jq .
```

### 4.2 Test from React Browser Console

Open **Browser DevTools** (F12) → **Console** tab, then execute:

```javascript
// Test 1: Fetch club info
fetch('http://localhost:8000/api/club')
  .then(r => r.json())
  .then(d => console.log('Club:', d))
  .catch(e => console.error('Error:', e));

// Test 2: Fetch matchs
fetch('http://localhost:8000/api/matchs')
  .then(r => r.json())
  .then(d => console.log('Matchs:', d.data))
  .catch(e => console.error('Error:', e));

// Test 3: Test login
fetch('http://localhost:8000/api/auth?action=login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ username: 'Administrateur', password: 'your_password' })
})
  .then(r => r.json())
  .then(d => {
    console.log('Login response:', d);
    localStorage.setItem('auth_token', d.token);
  })
  .catch(e => console.error('Error:', e));
```

---

## 5. COMPONENT & PAGE TESTING

### 5.1 Navigation Test

Navigate through all pages:

- [ ] **Home** (`/`)
  - Check hero section renders
  - Check links work
  - Check footer is visible

- [ ] **Matchs** (`/matchs`)
  - Check calendar displays
  - Check filters (equipe, competition)
  - Check tab switching (upcoming/recent)
  - Check MatchCard components render
  - Check no API errors

- [ ] **Résultats** (`/resultats`)
  - Check past matches display
  - Check filtering works
  - Check correct matches shown (with scores)

- [ ] **Classements** (`/classements`)
  - Check table renders
  - Check standings data correct
  - Check competition filter works
  - Check highlighting for top 3 teams

- [ ] **Admin/Login** (`/admin/login`)
  - Check form renders
  - Check email/password inputs work
  - Check password show/hide toggle works
  - Check error handling on failed login
  - Check redirect to dashboard on success

- [ ] **Admin/Dashboard** (`/admin/dashboard`)
  - Check redirect to login if not authenticated
  - Check user email displays
  - Check logout button works
  - Check tab switching (overview/matchs/config)
  - Check stats cards render

### 5.2 Component Testing

**In React Components:**

```javascript
// Open DevTools Console and check each component:

// 1. Check if components mount without errors
console.log('App mounted');

// 2. Check context providers work
import { useAuth } from './context/AuthContext';
const { user, token } = useAuth();
console.log('Auth context:', { user, token });

// 3. Check API calls work
import { useApi } from './hooks/useApi';
const { data, loading, error } = useApi('/api/matchs');
console.log('Matchs API:', { data, loading, error });
```

---

## 6. PERFORMANCE & QUALITY AUDITS

### 6.1 Lighthouse Audit

**In Chrome DevTools:**

1. Open DevTools (F12)
2. Go to **Lighthouse** tab
3. Click **Analyze page load**
4. Wait for report

**Expected Scores:**

- Performance: > 85
- Accessibility: > 90
- Best Practices: > 85
- SEO: > 85

### 6.2 Console Errors Check

**In Browser DevTools Console (F12):**

- [ ] No red errors
- [ ] No CORS errors
- [ ] No 404s for assets
- [ ] No undefined variable warnings
- [ ] No React warnings

**Common errors to watch for:**

```javascript
// ❌ CORS Error - API endpoint misconfigured
"Access to XMLHttpRequest at 'http://api.example.com'
from origin 'http://localhost:5173' has been blocked"

// ❌ 404 Error - API endpoint doesn't exist
"GET http://localhost:8000/api/invalid-endpoint 404"

// ❌ Authorization Error - Missing/expired token
"401 Unauthorized"

// ✅ Expected on first load - no errors visible
```

### 6.3 Network Tab Analysis

**In DevTools Network tab (F12 → Network):**

1. Reload page
2. Check all requests complete with 200/304 status
3. Expected requests:
   - HTML document (index.html)
   - JS bundles (app.js, vendor.js, router.js)
   - CSS files
   - API calls to `/api/*` endpoints

### 6.4 Bundle Size Check

```bash
cd C:\Développement\fcchiche-react

# Production build analysis
npm run build

# Check output
npm list --all 2>/dev/null | head -20

# Should see:
# ✓ dist/index.html
# ✓ dist/assets/app-*.js
# ✓ dist/assets/vendor-*.js
# ✓ dist/assets/style-*.css
# Total: ~88KB gzipped (verified in PROGRESS.md)
```

### 6.5 Build Performance

```bash
# Check build time
time npm run build

# Expected: < 1 second
```

---

## 7. CROSS-BROWSER TESTING

### 7.1 Browser Compatibility

Test on:

- [ ] **Chrome** (latest)
  - Check all features work
  - Check responsive design
  - Check console for errors

- [ ] **Firefox** (latest)
  - Check styling matches
  - Check interactions work
  - Check forms submit correctly

- [ ] **Safari** (latest - if Mac available)
  - Check layout correct
  - Check animations smooth
  - Check touch interactions

- [ ] **Edge** (latest)
  - Check compatibility
  - Check mobile mode

### 7.2 Responsive Design Testing

**Desktop (1920px):**
- All content visible
- No horizontal scroll
- Navigation expanded

**Tablet (768px):**
- [ ] Layout adapts
- [ ] Navigation still accessible
- [ ] Tables scroll horizontally if needed
- [ ] Touch targets > 48px

**Mobile (375px):**
- [ ] Hamburger menu appears
- [ ] Content stacks vertically
- [ ] Forms are usable
- [ ] No horizontal scroll
- [ ] Text is readable

**Test in Chrome DevTools:**

Press F12 → Toggle Device Toolbar (Ctrl+Shift+M) → Select device

---

## 8. TROUBLESHOOTING

### 8.1 React Development Server Won't Start

```bash
# Problem: npm run dev fails
# Solution:

# 1. Clear cache and node_modules
rm -rf node_modules package-lock.json
npm cache clean --force

# 2. Reinstall
npm install

# 3. Try again
npm run dev

# 4. If still fails, check Node version
node --version  # Should be v18+
```

### 8.2 API Not Responding (CORS Error)

```javascript
// Error in console:
// "Access to fetch at 'http://localhost:8000/api/matchs'
// from origin 'http://localhost:5173' has been blocked"

// Solution:
// 1. Verify backend is running:
curl http://localhost:8000/api/config

// 2. Check CORS headers from backend:
curl -i http://localhost:8000/api/matchs | grep "Access-Control"

// 3. Check .env file points to correct API:
cat .env | grep VITE_API_BASE_URL
// Should be: http://localhost:8000/api

// 4. Restart both servers
```

### 8.3 Login Failed

```javascript
// Error: Login returns 401 or error

// Solution:
// 1. Verify credentials (check backend docs)
curl -X POST "http://localhost:8000/api/auth?action=login" \
  -H "Content-Type: application/json" \
  -d '{"username":"Administrateur","password":"correct_password"}'

// 2. Check token in localStorage:
// In DevTools Console:
localStorage.getItem('auth_token')
// Should show a long JWT token

// 3. Check token in Authorization header:
// In Network tab, look for API requests
// Check Authorization header contains: Bearer [token]
```

### 8.4 Pages Not Loading Data

```javascript
// Problem: Pages show empty state

// Solution:
// 1. Check API response:
fetch('http://localhost:8000/api/matchs').then(r => r.json()).then(d => console.log(d))

// 2. Check browser Network tab
// Should see request to /api/matchs returning 200 with data

// 3. Check useApi hook:
// Components using useApi should show loading → data states

// 4. Verify data context initialization:
// In DevTools Console:
// Should see context providers wrapping app
```

### 8.5 Styles/CSS Not Loading

```bash
# Check CSS files are bundled
npm run build

# Verify dist/ folder:
ls -la C:\Développement\fcchiche-react\dist\assets\

# Should see .css files alongside .js files

# If missing, check:
# 1. CSS imports in components
# 2. vite.config.js configuration
# 3. Rebuild with: npm run build
```

---

## 9. TEST RESULTS CHECKLIST

### Phase 8 Completion Criteria

**Backend Verification:**
- [ ] All 14 API endpoints respond with 200
- [ ] Database connection successful
- [ ] CORS headers present in responses
- [ ] Authentication (JWT) working
- [ ] CRUD operations (create/read/update/delete) working

**Frontend Development:**
- [ ] `npm install` completes without errors
- [ ] `npm run dev` starts without errors
- [ ] React app accessible at http://localhost:5173
- [ ] No console errors on page load

**API Integration:**
- [ ] All 10 GET endpoints return data
- [ ] Login endpoint returns JWT token
- [ ] Authorization header properly sent
- [ ] CORS preflight succeeds
- [ ] No 401/403/404 errors

**Component Testing:**
- [ ] All 6 pages accessible
- [ ] All routes working (/, /matchs, /resultats, /classements, /admin/login, /admin/dashboard)
- [ ] Navigation menu works
- [ ] Footer displays
- [ ] Components render without errors
- [ ] Forms submit correctly

**Performance:**
- [ ] Lighthouse score > 85
- [ ] No console errors
- [ ] Bundle size < 100KB
- [ ] Build time < 1 second
- [ ] Dev server HMR < 100ms

**Responsive Design:**
- [ ] Mobile (375px) - hamburger menu works
- [ ] Tablet (768px) - layout adapts
- [ ] Desktop (1920px) - full layout
- [ ] Touch targets > 48px
- [ ] No horizontal scroll on mobile

**Cross-Browser:**
- [ ] Chrome - works
- [ ] Firefox - works
- [ ] Safari/Edge - works (if available)

---

## 10. NEXT STEPS

Once Phase 8 (Testing) is complete:

### Phase 9: PWA & Service Worker (2-3 hours)
```bash
# Create manifest.json
# Create public/service-worker.js
# Register SW in main.jsx
# Test offline functionality
# Test installability
```

### Phase 10: Deployment (1-2 hours)
```bash
# Build production:
npm run build

# Deploy to preprod:
git add .
git commit -m "Phase 8: Testing complete, ready for PWA"
git push origin preprod

# Deploy to production:
git push origin main
```

---

## 📞 Quick Reference

**Commands:**

```bash
# Development
npm run dev              # Start dev server (http://localhost:5173)
npm run build            # Production build
npm run preview          # Preview production build locally

# Testing
npm test                 # Run tests (if configured)
npm run lint             # Check code quality

# Backend PHP
php -S localhost:8000 -t public  # Local PHP server
```

**URLs:**

```
Frontend (dev):     http://localhost:5173/
Frontend (preview): http://localhost:4173/
Backend (local):    http://localhost:8000/api/
Backend (preprod):  https://preprod.fcchiche.fr/api/
Docs:               http://localhost:8000/api/docs.html
```

**Important Files:**

```
React app:          C:\Développement\fcchiche-react\
React src:          C:\Développement\fcchiche-react\src\
PHP backend:        C:\Développement\fcchiche\
PHP API:            C:\Développement\fcchiche\public\api\
React config:       C:\Développement\fcchiche-react\vite.config.js
React env:          C:\Développement\fcchiche-react\.env
```

---

## ✅ Summary

This guide covers all testing required for Phase 8:

1. **Backend Verification** - Ensure PHP API endpoints respond
2. **Frontend Setup** - Start React dev server
3. **API Integration** - Test all 14 endpoints
4. **Component Testing** - Verify all pages work
5. **Performance** - Lighthouse audit
6. **Responsive Design** - Mobile/tablet/desktop
7. **Cross-Browser** - Chrome, Firefox, Safari, Edge
8. **Troubleshooting** - Common issues and fixes

**Expected Outcome:** All tests pass, app production-ready for PWA and deployment

---

**Created:** 2025-11-08
**Version:** 1.0
**Status:** Ready for execution

