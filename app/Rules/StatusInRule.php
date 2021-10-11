<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StatusInRule implements Rule
{
    protected $map;
    protected $messge;
    protected $attribute;
    public function __construct(array $map, $message = '')
    {
        $this->map = $map;
        $this->message = $message;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        return array_key_exists($value, $this->map);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        preg_match('/.*\.(\d+)\..*/', $this->attribute, $m);
        $index = $m[1] ?? '';
        $msg = $this->message;
        if ($index === '') {
            return $msg;
        }
        $index += 1;
        return str_replace(':index', $index, $msg);
    }
}
