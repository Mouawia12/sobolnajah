<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMenuSection extends Model
{
    protected $table = 'role_menu_sections';

    protected $fillable = ['role_id', 'section_key'];
}
