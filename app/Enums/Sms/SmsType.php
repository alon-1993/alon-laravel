<?php
namespace App\Enums\Sms;

use MyCLabs\Enum\Enum;

/**
 * @method static SmsType LOGIN()
 * @method static SmsType ENTRY()
 * @method static SmsType SIGN()
 */
final class SmsType extends Enum
{
    private const LOGIN = 'login';
    private const ENTRY = 'entry';
    private const SIGN = 'sign';
}
