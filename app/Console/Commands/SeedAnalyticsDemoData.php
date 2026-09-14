<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SeedAnalyticsDemoData extends Command
{
    protected $signature = 'analytics:seed-demo {tenant} {count=40}';
    protected $description = 'Seed demo bookings and invoices for analytics testing';

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
            $staffList = Staff::with('services')->get();
            $services = Service::all();

            if ($staffList->isEmpty() || $services->isEmpty()) {
                $this->error('لا يوجد موظفين أو خدمات كافية!');
                return;
            }

            $statuses = ['completed', 'completed', 'completed', 'confirmed', 'cancelled', 'pending'];
            $paymentMethods = ['cash', 'card'];
            $minutesOptions = [0, 15, 30, 45];
            $names = ['أحمد محمد', 'سارة علي', 'خالد حسن', 'ليلى إبراهيم', 'يوسف كريم', 'نور صالح'];

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