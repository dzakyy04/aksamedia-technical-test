<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['image', 'name', 'phone', 'division_id', 'position'];
    protected $hidden = ['created_at', 'updated_at'];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
