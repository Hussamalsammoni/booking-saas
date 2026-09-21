<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class SeedMobileShopDemoData extends Command
{
    protected $signature = 'mobile-shop:seed-demo {tenant} {count=40}';
    protected $description = 'Seed demo services, staff, bookings and invoices for a mobile-repair-shop tenant';

    protected function defaultWorkingHours(): array
    {
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $hours = [];

        foreach ($days as $day) {
            $hours[$day] = [
                'enabled' => $day !== 'friday',
                'start' => '09:00',
                'end' => '18:00',
            ];
        }

        return $hours;
    }

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $count = (int) $this->argument('count');

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->error("Tenant '{$tenantId}' not found.");
            return 1;
        }

        $tenant->run(function () use ($count) {

            // =====================================================
            // 1) Services (create only if none exist yet)
            // =====================================================
            if (Service::count() === 0) {
                $servicesData = [
                    ['name' => 'تغيير شاشة', 'description' => 'استبدال شاشة الجهاز المكسورة أو التالفة', 'duration_minutes' => 45, 'price' => 45000],
                    ['name' => 'تغيير بطارية', 'description' => 'استبدال بطارية الجهاز بأخرى أصلية أو بديلة', 'duration_minutes' => 30, 'price' => 25000],
                    ['name' => 'فورمات وتثبيت نظام', 'description' => 'إعادة تهيئة الجهاز وتثبيت النظام من جديد', 'duration_minutes' => 40, 'price' => 15000],
                    ['name' => 'صيانة سوفتوير', 'description' => 'حل مشاكل البرمجيات، التعليق، وإعادة التشغيل التلقائي', 'duration_minutes' => 35, 'price' => 20000],
                    ['name' => 'تبديل منفذ الشحن', 'description' => 'إصلاح أو استبدال منفذ الشحن التالف', 'duration_minutes' => 50, 'price' => 30000],
                    ['name' => 'تنظيف داخلي وصيانة عامة', 'description' => 'فتح الجهاز وتنظيفه من الغبار والرطوبة', 'duration_minutes' => 25, 'price' => 12000],
                    ['name' => 'إصلاح مكبر الصوت', 'description' => 'إصلاح أو استبدال السماعة الداخلية أو الخارجية', 'duration_minutes' => 30, 'price' => 18000],
                    ['name' => 'استرجاع بيانات', 'description' => 'محاولة استرجاع الصور وجهات الاتصال من جهاز معطل', 'duration_minutes' => 60, 'price' => 40000],
                ];

                foreach ($servicesData as $data) {
                    Service::create(array_merge($data, ['is_active' => true]));
                }

                $this->info('تم إنشاء ' . count($servicesData) . ' خدمة.');
            }

            // =====================================================
            // 2) Staff (create only if none exist yet)
            // =====================================================
            if (Staff::count() === 0) {
                $techs = [
                    ['name' => 'محمد الفني', 'title' => 'فني صيانة هاردوير'],
                    ['name' => 'علي حداد', 'title' => 'فني صيانة سوفتوير'],
                    ['name' => 'رامي عيسى', 'title' => 'فني عام'],
                ];

                $allServiceIds = Service::pluck('id')->all();

                foreach ($techs as $index => $tech) {
                    $email = 'tech' . ($index + 1) . '_' . uniqid() . '@example.com';

                    $user = User::create([
                        'name' => $tech['name'],
                        'email' => $email,
                        'password' => Hash::make('password123'),
                        'role' => 'staff',
                    ]);

                    $staff = Staff::create([
                        'user_id' => $user->id,
                        'title' => $tech['title'],
                        'working_hours' => $this->defaultWorkingHours(),
                        'is_active' => true,
                        'can_view_all_bookings' => false,
                    ]);

                    // Give each technician a random subset (at least 3) of the services
                    $assignedCount = min(count($allServiceIds), rand(3, count($allServiceIds)));
                    $assigned = collect($allServiceIds)->shuffle()->take($assignedCount)->all();

                    $staff->services()->sync($assigned);
                }

                $this->info('تم إنشاء ' . count($techs) . ' فنيين.');
            }

            // =====================================================
            // 3) Bookings + Invoices
            // =====================================================
            $staffList = Staff::with('services')->get();
            $services = Service::all();

            if ($staffList->isEmpty() || $services->isEmpty()) {
                $this->error('لا يوجد فنيين أو خدمات كافية!');
                return;
            }

            $statuses = ['completed', 'completed', 'completed', 'confirmed', 'cancelled', 'pending'];
            $paymentMethods = ['cash', 'card'];
            $minutesOptions = [0, 15, 30, 45];
            $names = ['أحمد محمد', 'سارة علي', 'خالد حسن', 'ليلى إبراهيم', 'يوسف كريم', 'نور صالح', 'فراس منصور', 'هبة الديك'];

            for ($i = 0; $i < $count; $i++) {
                $staff = $staffList->random();

                if ($staff->services->isNotEmpty()) {
                    $service = $staff->services->random();
                } else {
                    $service = $services->random();
                }

                $daysAgo = rand(0, 29);
                $minute = $minutesOptions[array_rand($minutesOptions)];

                $start = Carbon::now()->subDays($daysAgo)->setTime(rand(9, 17), $minute);
                $end = (clone $start)->addMinutes($service->duration_minutes);

                $status = $statuses[array_rand($statuses)];

                $booking = Booking::create([
                    'customer_id' => null,
                    'customer_name' => $names[array_rand($names)],
                    'customer_phone' => '07' . rand(10000000, 99999999),
                    'staff_id' => $staff->id,
                    'service_id' => $service->id,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => $status,
                    'created_at' => $start,
                    'updated_at' => $start,
                ]);

                if ($status === 'completed') {
                    $isPaid = (bool) rand(0, 1);

                    Invoice::create([
                        'invoice_number' => 'INV-' . str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT) . '-' . rand(100, 999),
                        'booking_id' => $booking->id,
                        'amount' => $service->price,
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'status' => $isPaid ? 'paid' : 'unpaid',
                        'paid_at' => $isPaid ? $start : null,
                        'created_at' => $start,
                        'updated_at' => $start,
                    ]);
                }
            }

            $this->info("تم إنشاء {$count} حجز وفواتيرها المرتبطة بنجاح!");
        });

        return 0;
    }
}