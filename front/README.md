# Introduction
This repo is the front end application for the Aurora Medical Software.

**Only push to the DEV branch.**

Documentation:
This Documentation is a work in progress and is not complete.
https://api.dev.aurorasw.com.au/docs

BE Logs:
https://api.dev.aurorasw.com.au/log-viewer

Test Servers:
MAIN BRANCH (BE): https://api.demo.aurorasw.com.au/
DEV BRANCH (BE): https://api.dev.aurorasw.com.au/

MAIN BRANCH (FE): https://demo.aurorasw.com.au/
DEV BRANCH (FR): https://dev.aurorasw.com.au/

## Local project setup
```
yarn install
```

If you are building backend locally as well:
.env.local from .env
Configure .env.local
VUE_APP_API_URL = http://localhost:8000/

If you are only working on FE you can juts use the .env as is.

### Compiles and hot-reloads for development

```
yarn serve
```

# Test Users

PASSWORD: Paxxw0rd

USERS:
- admin (Role: Admin) 
- org-admin (Role: Organization Admin) 
- org-manager (Role: Organization Manager)  
- specialist_1 (Role: Specialist)
- anesthetists_1 (Role: Anesthetist)
