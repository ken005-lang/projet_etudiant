<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email' => [
                'required', 
                'email:rfc,dns', 
                'max:255',
                function ($attribute, $value, $fail) {
                    $mode = $this->input('mode');
                    $value = strtolower(trim($value));
                    $exists = false;
                    
                    if ($mode === 'visiteur') {
                        $exists = \App\Models\User::where('type_role', 'visiteur')
                            ->where('username', $value)
                            ->exists();
                    } else if ($mode === 'groupe') {
                        $exists = \App\Models\User::where('type_role', 'groupe')
                            ->where(function($q) use ($value) {
                                $q->where('email', $value)
                                  ->orWhereHas('groupProfile', function($sq) use ($value) {
                                      $sq->where('contact_email', $value);
                                  });
                            })->exists();
                    } else {
                        $exists = \App\Models\User::where('email', $value)->exists();
                    }
                    
                    if (!$exists) {
                        $fail('Aucun compte ne correspond à cette adresse e-mail.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.exists' => 'Aucun compte ne correspond à cette adresse e-mail.',
        ];
    }
}
