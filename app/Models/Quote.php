<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Quote extends Model {
    use SoftDeletes;
    protected $guarded = [];
    protected function casts(): array { return ['valid_until'=>'date','sent_at'=>'datetime','viewed_at'=>'datetime','decided_at'=>'datetime']; }
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function company(){ return $this->belongsTo(Company::class); }
    public function items(){ return $this->hasMany(QuoteItem::class); }
    public function invoice(){ return $this->hasOne(Invoice::class); }
    public function parent(){ return $this->belongsTo(Quote::class, 'parent_quote_id'); }
    public function revisions(){ return $this->hasMany(Quote::class, 'parent_quote_id')->orderBy('version'); }
    public function root(): Quote { return $this->parent ?: $this; }
    public function money(int $cents): string { return $this->currency.' '.number_format($cents / 100, 2); }
}
