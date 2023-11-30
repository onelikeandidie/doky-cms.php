<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleUpdateFormSubmitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('article'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:articles,slug,' . $this->route('article')->id,
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'content' => [
                'required',
                'string',
            ],
            'visibility' => [
                'required',
                'string',
                'in:public,private,restricted,unlisted',
            ],
            'priority' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],
            'tags' => [
                'nullable:string',
            ],
            'allowed_users' => [
                'nullable:string',
            ],
            'allowed_roles' => [
                'nullable:string',
            ],
        ];
    }
}
