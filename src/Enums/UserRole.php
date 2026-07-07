<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Vendor = 'vendor';
    case VendorStaff = 'vendor_staff';
    case Customer = 'customer';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Vendor => 'Vendor',
            self::VendorStaff => 'Vendor Staff',
            self::Customer => 'Customer',
            self::Delivery => 'Delivery',
        };
    }

    public static function fromString(string $role): self
    {
        return self::from($role);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
