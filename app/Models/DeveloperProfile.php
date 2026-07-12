<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeveloperProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'section',
        'module_name',
        'professor',
        'github_url',
        'summary',
        'photo_path',
    ];
}
