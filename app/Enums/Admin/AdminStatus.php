<?php
namespace App\Enums\Admin;

use MyCLabs\Enum\Enum;

/**
 * @method static AdminStatus ENABLED()
 * @method static AdminStatus DISABLED()
 */
final class AdminStatus extends Enum
{
    private const ENABLED = 'enabled';
    private const DISABLED = 'disabled';
}
