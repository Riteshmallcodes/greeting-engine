# WishCard

WishCard is a production-ready PHP festival greeting platform where users can create and share personalized wish posters for Diwali, Holi, Eid, New Year, and Raksha Bandhan.

## Features
- Festival-wise wish generation with poster-style output
- Secure image upload flow with validation and sanitization
- CSRF protection and session hardening
- Admin login and dashboard with search, pagination, and delete controls
- Share-ready wish URLs and downloadable cards
- Dynamic sitemap endpoint and robots configuration

## Tech Stack
- PHP (PDO + MySQL)
- HTML/CSS/JavaScript
- Apache (.htaccess)

## Quick Start
1. Copy `.env.example` to `.env` and set database + app URL values.
2. Import `festival_wishes.sql` into MySQL.
3. Run on an Apache + PHP server and open `index.php`.

## Default Database Schema
- `wishes`
- `admin_users`

## Security Notes
- Keep `.env` private and never commit real secrets.
- Use HTTPS in production.
- Ensure uploads directory does not execute PHP files.
