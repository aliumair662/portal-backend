<?php

namespace App\Enums;

class Interior
{
    private static $interior = [
        48, // Simplessa - 31 Ongebleekt katoen zonder koord
        46, // Nova - 23 ongebleekt katoen met koord (23)
        73, // Binnenbekleding: Finessa - 15 Ongebleekt katoen
    ];

    public static function getInterior(): array
    {
        return self::$interior;
    }

    public static function getIds(): array
    {
        return self::$interior;
    }

}
