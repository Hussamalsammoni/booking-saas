<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
        'features' => 'array',
        'gallery_paths' => 'array',
        'working_hours' => 'array',
        'social_links' => 'array',
        'testimonials' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'is_active',
            'subscription_ends_at',
            'shop_name',
            'logo_path',
            'cover_path',
            'primary_color',
            'secondary_color',
            'bg_color',
            'text_color',
            'description',
            'phone',
            'features',
            'gallery_paths',
            'working_hours',
            'map_address',
            'map_url',
            'social_links',
            'testimonials',
        ];
    }
}