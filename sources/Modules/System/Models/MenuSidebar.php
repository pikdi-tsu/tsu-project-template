<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;

class MenuSidebar extends Model
{
    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'icon',
        'type',
        'route',
        'permission_name',
        'parent_id',
        'order',
        'isactive'
    ];

    public function getTable()
    {
        return config('app.module.name') . '_menu_sidebars';
    }

    // Relasi ke Submenu
    public function children()
    {
        return $this->hasMany(__CLASS__, 'parent_id', 'id')->orderBy('order', 'asc');
    }

    // Relasi ke Parent
    public function parent()
    {
        return $this->belongsTo(__CLASS__, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('isactive', 1);
    }

    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }
}
