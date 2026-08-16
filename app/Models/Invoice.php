<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Invoice extends Model { protected $guarded = []; protected function casts(): array{return ['due_date'=>'date','paid_at'=>'datetime'];} public function quote(){return $this->belongsTo(Quote::class);} public function customer(){return $this->belongsTo(Customer::class);} }
