<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\BelongsToTenant;

class NotificationTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'channel',
        'category',
        'subject',
        'body_template',
        'variables',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function dispatchLogs(): HasMany
    {
        return $this->hasMany(NotificationDispatchLog::class, 'template_id');
    }

    /**
     * Render the template body replacing placeholders like {variable_name} with actual values.
     */
    public function render(array $params = []): string
    {
        $text = $this->body_template ?? '';
        foreach ($params as $key => $val) {
            $valStr = is_scalar($val) ? (string) $val : json_encode($val);
            $text = str_replace('{' . $key . '}', $valStr, $text);
        }
        return $text;
    }
}
