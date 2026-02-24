<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInscription extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        $email     = $this->email;
        $prenom    = $this->prenom;
        $nom       = $this->nom;

        return [
            'numtelephone' => [
                'required',
                // نتأكد يكون unique مع استثناء حالة التعديل
                Rule::unique('inscriptions', 'numtelephone')
                    ->ignore($this->id) // إذا عندك update
                    ->where(function ($query) use ($email, $prenom, $nom) {
                        return $query->where('email', $email)
                                     ->where(function ($q) use ($prenom, $nom) {
                                         $q->where('prenom', $prenom)
                                           ->orWhere('nom', $nom);
                                     });
                    }),
            ],
            'email' => 'required|email',
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
        ];
    }
}
