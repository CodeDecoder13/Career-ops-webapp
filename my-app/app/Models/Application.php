<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class Application extends Model
{
    protected $fillable = [
        'num',
        'company',
        'role',
        'score',
        'status',
        'date',
        'report_link',
        'pdf_link',
        'notes',
    ];

    protected $casts = [
        'num' => 'integer',
        'score' => 'integer',
        'date' => 'date',
    ];

    public function scopeOrderedByDate(Builder $query): Builder
    {
        return $query->orderByDesc('date');
    }
}
