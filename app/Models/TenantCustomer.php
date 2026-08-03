<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantCustomer extends Model
{
    protected $fillable = ['tenant_id', 'name', 'email', 'phone', 'password', 'default_address', 'default_pincode'];

    protected $hidden = ['password'];

    public function orders(): HasMany
    {
        return $this->hasMany(TenantOrder::class);
    }
}
