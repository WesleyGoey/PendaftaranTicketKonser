<div align="center">

# 🎫 Concert Ticket Registration

**A small Laravel and Vue app for buying concert tickets, built to test transactional email with Purelymail.**

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/vuejs-%2335495e.svg?style=flat&logo=vuedotjs&logoColor=%234FC08D)
![TypeScript](https://img.shields.io/badge/typescript-%23007ACC.svg?style=flat&logo=typescript&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-9553E9?style=flat&logo=inertia&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/tailwindcss-%2338B2AC.svg?style=flat&logo=tailwind-css&logoColor=white)
![GitHub Actions](https://img.shields.io/badge/github%20actions-%232671E5.svg?style=flat&logo=githubactions&logoColor=white)

</div>

> Personal project · Oct – Nov 2026

## Overview

Visitors pick a seat type and quantity for a demo concert, *Bruno Mars Live in Surabaya*, and receive a confirmation email right after booking. The project's main purpose was to try sending real email through [Purelymail](https://purelymail.com/) SMTP from a Laravel app, with automated tests and deployment around it.

## Features

- **Ticket form without login**: name, email, phone, notes, seat type, and quantity
- **Seat types**: Regular (Rp1,000,000), Premium (Rp2,000,000), VIP (Rp3,000,000), and Diamond (Rp4,000,000); the total is price × quantity
- **No overselling**: the concert row is locked inside a database transaction before the stock is reduced
- **Confirmation email** sent through Purelymail SMTP
- **Accounts** from the Laravel Vue starter kit (Fortify): register, log in, email verification, password reset, two-factor authentication, and profile, security, and appearance settings
- **Feature tests** for booking, the demo concert, email content, sold-out handling, and required fields
- **CI/CD**: every push to `main` runs the tests and builds the assets on GitHub Actions, then deploys to the server over SSH

## Tech Stack

| | |
| --- | --- |
| Backend | Laravel 13 (PHP 8.3+), Laravel Fortify |
| Frontend | Vue 3, TypeScript, Inertia.js, Tailwind CSS 4, Reka UI |
| Build | Vite and Vite+ |
| Email | Purelymail SMTP |
| Database | SQLite by default |
| CI/CD | GitHub Actions (tests, build, SCP and SSH deploy) |

## Getting Started

Requires PHP 8.3+, Composer, and Node.js.

```bash
git clone https://github.com/WesleyGoey/PendaftaranTicketKonser.git
cd PendaftaranTicketKonser
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

To send real emails, fill in your Purelymail mailbox in `.env`:

```env
MAIL_USERNAME=you@your-domain.com
MAIL_PASSWORD=your-mailbox-password
MAIL_FROM_ADDRESS="you@your-domain.com"
```

Then build the front end and start the server:

```bash
npm run build
php artisan serve
```

Open `http://localhost:8000`. Run the tests with `php artisan test`.

## Project Structure

```text
app/Http/Controllers/
├── ConcertController.php       # Concert list and detail
└── RegistrationController.php  # Booking, stock lock, confirmation email
app/Mail/RegistrationEmail.php  # Confirmation email
app/Models/                     # Concert, TicketPurchase, User
resources/js/pages/             # Vue pages (concerts, auth, settings)
resources/views/                # Blade pages (home, concerts, email)
tests/Feature/                  # Booking tests
.github/workflows/deploy.yaml   # CI/CD pipeline
```
