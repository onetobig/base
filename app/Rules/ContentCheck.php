<?php

namespace App\Rules;

use App\Services\MiniProgramService;
use Illuminate\Contracts\Validation\Rule;

class ContentCheck implements Rule
{
    protected $msg;
    public function __construct($msg = '含有违法违规的文字内容，请检查')
    {
        $this->msg = $msg;
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
        return app(MiniProgramService::class)
            ->checkText($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->msg;
    }
}
