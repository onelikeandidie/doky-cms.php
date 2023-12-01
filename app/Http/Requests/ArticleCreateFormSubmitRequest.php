<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;

class ArticleCreateFormSubmitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', Article::class);
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
                'unique:articles',
                'regex:/^[a-z0-9-\/]+$/',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
