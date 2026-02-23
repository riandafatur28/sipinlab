<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PolijeEmailRule implements ValidationRule
{
    protected $allowedDomains = [
        'student.polije.ac.id',
        'polije.ac.id'
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = $value;
        $domain = substr(strrchr($email, "@"), 1);

        if (!in_array($domain, $this->allowedDomains)) {
            $fail('Email harus menggunakan domain @student.polije.ac.id atau @polije.ac.id');
        }
    }
}
