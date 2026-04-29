# PropIntel CRM

Real estate investment lead-generation and deal-analysis platform.

## Tech Stack
- PHP 8.0+
- MySQL 8
- Bootstrap 5
- HTMX 1.9
- SendGrid (email)
- Twilio (SMS)
- OpenAI / Claude (AI analysis)

## Setup

### 1. Database
```sql
mysql -u root -p -e "CREATE DATABASE propintel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p propintel < database/schema.sql
mysql -u root -p propintel < database/seed.sql
```

### 2. Configuration
Copy and customize the local config:
```bash
cp app/config/config.php app/config/config.local.php
```

Edit `config.local.php` or set environment variables:
```
DB_HOST=127.0.0.1
DB_NAME=propintel
DB_USER=root
DB_PASS=your_password

SENDGRID_API_KEY=SG.xxxxx
SENDGRID_FROM_EMAIL=hello@yourdomain.com

TWILIO_ACCOUNT_SID=ACxxxxx
TWILIO_AUTH_TOKEN=xxxxx
TWILIO_FROM_NUMBER=+15551234567

OPENAI_API_KEY=sk-xxxxx
# Or use Claude:
AI_PROVIDER=claude
CLAUDE_API_KEY=sk-ant-xxxxx
```

### 3. Web Server
Point document root to `/public`.

Apache `.htaccess` is included for URL rewriting.

Nginx example:
```nginx
root /var/www/propintel/public;
index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ { fastcgi_pass unix:/run/php/php8.2-fpm.sock; include fastcgi_params; fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; }
```

### 4. Storage Permissions
```bash
chmod -R 775 storage/
```

### 5. Login
Default credentials (change immediately):
- Email: `admin@propintel.com`
- Password: `Admin1234!`

## Features

### Phase 1 — Foundation
- [x] MySQL schema (21 tables)
- [x] PDO singleton with prepared statements
- [x] Session-based auth with CSRF protection
- [x] Role-based access (admin, acquisitions, marketing, viewer)
- [x] Dashboard with stats, activity log, tasks

### Phase 2 — Lead Management
- [x] Full CRUD for leads
- [x] HTMX-powered filter panel (zip, city, status, flags, score range, equity range)
- [x] Lead detail page with inline status update, notes timeline, task management
- [x] Lead scoring (0–100, weighted factors, stored explanation)

### Phase 3 — Import
- [x] CSV upload with source type detection
- [x] Column mapping UI with auto-detection for PropStream, Regrid
- [x] Duplicate detection (address+ZIP and APN)
- [x] Import history log

### Phase 4 — Campaigns & Outreach
- [x] Campaign manager (direct mail, email, SMS, cold call, mixed)
- [x] SendGrid email integration with template variable merge
- [x] Twilio SMS integration with inbound webhook
- [x] 8 built-in letter templates (absentee, probate, pre-foreclosure, tax delinquent, vacant, tired landlord)
- [x] Printable letter generation
- [x] Mail merge CSV export

### Phase 5 — Deal Analysis
- [x] Buy & Hold calculator (NOI, cap rate, DSCR, cash-on-cash, cash flow)
- [x] BRRRR calculator (refi projections, equity created, cash left in)
- [x] Flip calculator (all-in, selling costs, profit, ROI)
- [x] Wholesale calculator (MAO, assignment fee)
- [x] Saved scenarios per lead

### Phase 6 — AI Analysis
- [x] OpenAI (GPT-4o) and Claude (Anthropic) support
- [x] Investment summary
- [x] Seller motivation estimate
- [x] Suggested offer range
- [x] Repair risk notes
- [x] Seller letter generation
- [x] SMS opener
- [x] Cold call script

## Project Structure
```
/public          Web root
/app
  /config        config.php, database.php
  /core          Auth, Router, View, Validator, Logger
  /controllers   7 controllers
  /models        5 models
  /services      5 services (SendGrid, Twilio, AI, Scoring, CSV)
  /views         All PHP view templates
/database        schema.sql, seed.sql
/storage         uploads/, logs/, exports/
```

## Twilio Inbound Webhook
Set your Twilio phone number's inbound SMS webhook to:
```
POST https://yourdomain.com/webhooks/twilio/sms
```

## SendGrid Event Webhook
```
POST https://yourdomain.com/webhooks/sendgrid
```
