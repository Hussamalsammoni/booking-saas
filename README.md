# 📅 Booking SaaS — Multi-Tenant Appointment Platform

A multi-tenant SaaS built with **Laravel 11**, **Inertia.js** and **Vue 3** for service-based businesses (barbers, salons, tattoo studios, clinics). Every business gets an isolated database, its own branded public booking page, and a dashboard to manage staff, services, bookings and invoices.

Arabic-first (RTL) interface with WhatsApp notifications, built for the Arab market.


---

## 🚀 Key Features

* **Multi-Tenancy Engine:** database-per-tenant isolation using `stancl/tenancy`, with one domain per business.
* **Branded Public Booking Page:** each owner controls colors, logo, cover, gallery, working hours (with a live "open now" status), map location, social links and testimonials. The page re-themes itself, including dark mode.
* **4-Step Booking Flow:** service → staff member → available time slot → confirmation, with real-time availability.
* **Conflict-Free Scheduling:** server-side overlap checks with a buffer between appointments, so a slot can never be double-booked.
* **RESTful API:** structured endpoints for bookings, services, staff and invoices, secured with **Laravel Sanctum** bearer tokens.
* **Role-Based Access Control:** separate privileges for Super Admins, Tenant Owners and Staff (including the granular `can_view_all_bookings` permission).
* **Automated WhatsApp Messaging:** instant booking confirmations plus queued reminders (`SendBookingReminderJob`) through the UltraMsg API.
* **Invoicing & PDF Engine:** automatic billing and status updates, with Arabic PDF generation via `barryvdh/laravel-dompdf` and the Amiri font.
* **Super Admin Portal:** tenant management (`is_active`, `subscription_ends_at`) and revenue analytics.
* **Secure Authentication:** login, registration and OTP-based password recovery.

---

## 🧠 Engineering Highlights

* **Unguessable confirmation URLs:** the booking success page uses Laravel signed URLs instead of sequential IDs.
* **Exact map pin:** owners paste a normal Google Maps link and the server resolves it (short links included) into coordinates, restricted to Google hosts to prevent SSRF.
* **Resilient notifications:** WhatsApp failures are logged and never block a booking.

---

## 🛠️ Tech Stack

* **Backend:** Laravel 11.x (PHP 8.2+), Laravel Sanctum
* **Frontend:** Vue.js 3 (`<script setup>`), Inertia.js, Tailwind CSS, Axios
* **Database:** MySQL, separate database per tenant
* **Integrations:** UltraMsg (WhatsApp), Google Maps embed
* **Tooling:** Vite, Ziggy, Dompdf

---

## 📁 Core Models

* **Central:** `Tenant`, `Domain`, `Admin`
* **Tenant:** `User`, `Staff`, `Service`, `Booking`, `Invoice`

---

## ⚙️ Installation & Setup

1. **Clone and install dependencies**

   ```bash
   git clone https://github.com/Hussamalsammoni/booking-saas.git
   cd booking-saas
   composer install
   npm install
   ```

2. **Configure the environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Set your database credentials and the WhatsApp keys in `.env`:

   ```env
   ULTRAMSG_INSTANCE_ID=your_instance_id
   ULTRAMSG_TOKEN=your_token
   ```

3. **Run the central migrations**

   ```bash
   php artisan migrate
   ```

4. **Create your first tenant** (its database is created and migrated automatically):

   ```php
   $tenant = App\Models\Tenant::create(['id' => 'demo', 'shop_name' => 'Demo Shop']);
   $tenant->domains()->create(['domain' => 'demo.localhost']);
   ```

5. **Start everything**

   ```bash
   npm run dev
   php artisan serve
   php artisan queue:work   # needed for WhatsApp reminders
   ```

   Then open `http://demo.localhost:8000/book`.

---

## 🗺️ Roadmap

- [ ] Availability driven by each business's working hours
- [ ] Online deposits and payments
- [ ] Customer reviews collected after each visit

---

## 👤 Author

GitHub: [@Hussamalsammoni](https://github.com/Hussamalsammoni)