<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Money implements Rule
{
    protected $min;
    protected $max;
    protected $dot;
    public function __construct($min = 0, $max = 99999999.99, $dot = 2)
    {
        $this->min = $min;
        $this->max = $max;
        $this->dot = $dot;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //['required', 'numeric', 'between:0,99999999.99', 'regex:']

        if (!$value) {
            return true;
        }

//        if (!is_string($value)) {
//            return false;
//        }

        return (preg_match('/^\d{0,8}(\.\d{1,' .$this->dot .'})?$/', $value) > 0)
            && ($value >= $this->min)
            && ($value <= $this->max);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ":attribute 必须是 {$this->min} 到 {$this->max} 的数字，最多只支持{$this->dot}位小数";
    }
}
