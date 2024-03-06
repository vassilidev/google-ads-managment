<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Ad extends Model
{
    use LogsActivity;

    protected $fillable = [
        'ad_id',
        'name',
        'target_cpa',
        'spend_budget',
        'budget',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logAll();
    }

    public function getRouteKeyName(): string
    {
        return 'ad_id';
    }
}
