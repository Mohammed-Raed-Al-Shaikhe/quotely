<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Customer extends Model { use SoftDeletes; protected $guarded = []; public function quotes(){ return $this->hasMany(Quote::class); } }
