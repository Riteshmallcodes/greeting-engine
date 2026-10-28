<div align="center">

# 🎆 Greeting Engine (WishCard)
### **Production-Ready Dynamic Festival Greeting & Poster Generation Platform**

A scalable PHP web application engineered to generate, personalize, and share festival wish cards dynamically across multi-channel social networks with built-in security, administration, dynamic SEO, and cloud deployment.

<p align="center">
  <a href="https://wishcard.livoras.online/"><img src="https://img.shields.io/badge/Live%20Demo-wishcard.livoras.online-007ACC?style=for-the-badge&logo=google-chrome&logoColor=white" /></a>
  <img src="https://img.shields.io/badge/PHP-7.4%20%7C%208.x-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Security-CSRF%20%26%20Session%20Hardened-success?style=for-the-badge" />
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" />
</p>

</div>

---

### 🌟 Core Capabilities & Highlights

- 🌐 **Live Production Deployment:** Hosted and operational at **[wishcard.livoras.online](https://wishcard.livoras.online/)**.
- 🎨 **Dynamic Graphic Personalization:** On-the-fly customized greeting card generation supporting Diwali, Holi, Eid, New Year, and Raksha Bandhan.
- 🔒 **Enterprise-Grade Security:**
  - Strict session hardening & CSRF token validation.
  - Secure asset upload pipelines with mime-type checking.
  - SQL injection prevention using prepared statements.
- 📊 **Administrative Control Panel:**
  - Secure `/admin` dashboard with authentication.
  - Dynamic record pagination, keyword search, and engagement tracking.
- ⚡ **SEO & Social Optimization:**
  - Automated XML sitemap generator (`sitemap.php`).
  - OpenGraph dynamic meta tags for WhatsApp, Facebook, and Twitter link previews.
  - Crawl control via optimized `robots.txt` and `.htaccess` rewrites.
- 🏥 **Observability:** Built-in health check endpoint (`health.php`) for uptime and database connectivity monitoring.

---

### 🏗️ Architecture & Directory Structure

```text
greeting-engine/
├── admin/                 # Protected administrative dashboard & analytics
├── assets/                # Stylesheets, client-side scripts, and design templates
├── includes/              # Core business logic, database connectors, and security guards
├── uploads/               # Isolated storage directory for generated/uploaded assets
├── festival.php           # Festival-specific route handling & template resolution
├── festival_wishes.sql    # Relational database schema & default tables
├── health.php             # Production health-check endpoint
├── index.php              # Application entry point & card generator interface
├── sitemap.php            # Dynamic XML sitemap generator for SEO
├── wish.php               # Public view & share page for personalized cards
├── .env.example
