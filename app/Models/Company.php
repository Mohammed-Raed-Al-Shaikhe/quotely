<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Company extends Model { protected $guarded = []; protected function casts(): array{return ['trial_ends_at'=>'datetime'];} public function quotes(){ return $this->hasMany(Quote::class); } public function customers(){return $this->hasMany(Customer::class);} public function products(){return $this->hasMany(Product::class);} public function users(){return $this->hasMany(User::class);} public function subscriptionIsActive(): bool{return $this->subscription_status==='active'||($this->subscription_status==='trialing'&&$this->trial_ends_at?->isFuture());} }
