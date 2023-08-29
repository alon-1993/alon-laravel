<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CertNo implements Rule
{
    protected string $attrName;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($attrName = null)
    {
        $this->attrName = $attrName;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return preg_match('/(^\d{17}([0-9]|X)$)/i', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        $attrName = $this->attrName ?? ':attribute';
        return $attrName . '格式错误，必须为 18 位数字,末位 X 除外 ';
    }
}
