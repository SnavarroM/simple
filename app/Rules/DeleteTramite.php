<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DeleteTramite implements Rule
{
    public $originalTramiteId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($tramiteId)
    {
        $this->originalTramiteId = $tramiteId;
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
        return ($this->originalTramiteId == $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El ID del trámite ingresado no coincide con el ID del trámite original.';
    }
}
