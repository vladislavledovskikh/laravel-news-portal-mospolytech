<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** Технические имена ролей — по ним проверяем права. */
    public const MODERATOR = 'moderator';

    public const READER = 'reader';

    protected $fillable = ['code', 'title'];

    /**
     * Пользователи с этой ролью.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
