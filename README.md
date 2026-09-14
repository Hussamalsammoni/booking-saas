# 📅 Booking SaaS Backend (Multi-Tenant System)

A multi-tenant SaaS application built with **Laravel 11**, **Inertia.js**, and **Vue.js**, designed to manage service-based businesses, staff schedules, client bookings, and invoicing.

---

## 🚀 Key Features

* **Multi-Tenancy Engine:** Database-per-tenant isolation using `stancl/tenancy`.
* **RESTful API Integration:** Fully structured endpoints for bookings, services, staff, and invoices using **Laravel Sanctum** Bearer Token authentication.
* **Role-Based Access Control:** Distinct privileges for System Super Admins, Tenant Owners, and Staff members (`can_view_all_bookings` granular permission).
* **Automated WhatsApp Reminders:** Background job queues (`SendBookingReminderJob`) utilizing UltraMsg API for automated appointment notifications and post-booking follow-ups.
* **Invoicing & PDF Engine:** Automatic billing and status updates with localized PDF generation powered by `barryvdh/laravel-dompdf` and Amiri font support.
* **Administrative Dashboards:** Super Admin portal for tenant status management (`is_active`, `subscription_ends_at`) and interactive analytics interfaces for revenue metrics.

---

## 🛠️ Tech Stack

* **Backend:** Laravel 11.x (PHP 8.2+) & Laravel Sanctum
* **Frontend:** Vue.js 3, Inertia.js, Tailwind CSS
* **Database:** MySQL (Separate Database per Tenant)
* **Architecture:** RESTful APIs & Tenant Route Isolation
* **PDF & Tools:** Dompdf, Vite, Ziggy

---

## 📁 Core Database Models & Entities

* **Central Level:** `Tenant`, `Domain`, `Admin`
* **Tenant Level:** `User`, `Staff`, `Service`, `Booking`, `Invoice`

---

## ⚙️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone [https://github.com/Hussamalsammoni/booking-saas.git](https://github.com/Hussamalsammoni/booking-saas.git)
   cd booking-saas