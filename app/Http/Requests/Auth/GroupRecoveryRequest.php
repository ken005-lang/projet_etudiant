<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class GroupRecoveryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'group_name' => ['required', 'string', 'max:255'],
            'chef_nom' => ['required', 'string', 'max:255'],
            'chef_prenom' => ['required', 'string', 'max:255'],
            'filiere' => ['required', 'string', 'max:255'],
            'niveau' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
        ];
    }
}
