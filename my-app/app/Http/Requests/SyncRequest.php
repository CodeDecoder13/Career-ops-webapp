<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SyncRequest extends FormRequest
{
    /**
     * Authorization is enforced by the sync.token middleware, not here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'jobs' => ['required', 'array'],
            'jobs.*.num' => ['required', 'integer'],
            'jobs.*.company' => ['required', 'string'],
            'jobs.*.role' => ['required', 'string'],
            'jobs.*.score' => ['nullable', 'integer'],
            'jobs.*.status' => ['nullable', 'string'],
            'jobs.*.date' => ['nullable', 'date'],
            'jobs.*.report_link' => ['nullable', 'string'],
            'jobs.*.pdf_link' => ['nullable', 'string'],
            'jobs.*.notes' => ['nullable', 'string'],
            'stats' => ['required', 'array'],
        ];
    }
}
