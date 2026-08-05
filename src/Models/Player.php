<?php

namespace R0B0TB0SS\GamePlayerManager\Models;

use Illuminate\Database\Eloquent\Model;
use Livewire\Wireable;

class Player extends Model implements Wireable
{
    protected $guarded = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    public function toLivewire()
    {
        // Ensure ID is included explicitly if mixed
        $attributes = $this->attributes;
        if (isset($this->id)) {
            $attributes['id'] = $this->id;
        }
        return $attributes;
    }

    public static function fromLivewire($value)
    {
        $instance = new static($value);
        $instance->exists = true; // Pretend to exist to satisfy Filament checks
        return $instance;
    }
    
    // Override key name just in case, though UUID is used
    public function getKeyName()
    {
        return 'id';
    }
}
