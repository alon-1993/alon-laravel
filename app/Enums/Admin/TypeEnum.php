<?php
namespace App\Enums\Admin;

use MyCLabs\Enum\Enum;

/**
 * @method static TypeEnum ORGANIZATION()
 * @method static TypeEnum OPERATE()
 * @method static TypeEnum MERCHANT()
 */
final class TypeEnum extends Enum
{
    private const OPERATE = 'operate';
    private const MERCHANT = 'merchant';
    private const ORGANIZATION = 'organization';
}
