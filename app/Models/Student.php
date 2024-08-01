<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $fillable = [
        'id', 'user_id', 'name', 'subject', 'marks', 'created_at', 'updated_at',
    ];

    public function setSubjectAttribute($value)
    {
        $this->attributes['subject'] = Str::title($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
