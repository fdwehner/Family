<?php

namespace App\Services;

use App\Support\GroceryCatalog;
use App\Support\UploadRules;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class FormValidationService
{
    /**
     * @return array<string, mixed>
     */
    public function getValidationRules(string $form): array
    {
        return match ($form) {
            'grocery_product' => [
                'name' => ['required', 'string', 'max:255'],
                'brand' => ['nullable', 'string', 'max:255'],
                'unit' => ['required', 'string', Rule::in(GroceryCatalog::units())],
                'category' => ['required', 'string', Rule::in(GroceryCatalog::categories())],
                'photo' => ['nullable', ...UploadRules::groceryPhoto()],
                'is_featured' => ['boolean'],
                'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            ],
            'login' => [
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string'],
            ],
            'register' => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            default => throw new InvalidArgumentException('Unknown form: '.$form),
        };
    }

    /**
     * @return array<string, string>
     */
    public function getValidationMessages(string $form): array
    {
        return match ($form) {
            'grocery_product' => [
                'name.required' => __('grocery.validation.name_required'),
                'category.required' => __('grocery.validation.category_required'),
                'category.in' => __('grocery.validation.category_invalid'),
                'unit.required' => __('grocery.validation.unit_required'),
                'unit.in' => __('grocery.validation.unit_invalid'),
            ],
            'login' => [
                'email.required' => __('auth.forms.email_required'),
                'password.required' => __('auth.forms.password_required'),
            ],
            'register' => [
                'name.required' => __('auth.forms.name_required'),
                'email.required' => __('auth.forms.email_required'),
                'email.unique' => __('auth.forms.email_unique'),
                'password.required' => __('auth.forms.password_required'),
                'password.confirmed' => __('auth.forms.password_confirmed'),
            ],
            default => [],
        };
    }
}
