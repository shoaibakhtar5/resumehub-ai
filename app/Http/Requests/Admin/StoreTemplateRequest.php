<?php

namespace App\Http\Requests\Admin;

use App\Models\Template;
use App\Services\TemplateUploadService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Template::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('templates', 'name')->whereNull('deleted_at')],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('templates', 'slug')],
            'template_category_id' => ['nullable', 'integer', 'exists:template_categories,id'],
            'description' => ['nullable', 'string', 'max:3000'],
            'template_file' => ['required', 'file', 'max:5120', 'extensions:html,htm,txt,tex'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_family' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9 ,\-\'\"]+$/'],
            'supported_sections' => ['required', 'array', 'min:1'],
            'supported_sections.*' => ['string', Rule::in(['summary', 'experiences', 'education', 'skills', 'projects', 'certifications', 'languages', 'awards', 'references'])],
            'preview_images' => ['nullable', 'array', 'max:5'],
            'preview_images.*' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published', 'disabled'])],
            'is_featured' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->hasFile('template_file') || $validator->errors()->has('template_file')) {
                return;
            }
            try {
                app(TemplateUploadService::class)->validateSource($this->file('template_file'));
            } catch (ValidationException $exception) {
                foreach ($exception->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add($field, $message);
                    }
                }
            }
        }];
    }

    public function messages(): array
    {
        return ['template_file.extensions' => 'Upload an HTML (.html), TXT (.txt), or LaTeX (.tex) template file.'];
    }
}
