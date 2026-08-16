<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    public function create()
    {
        $company = auth()->user()->company;

        return view('quotes.create', [
            'customers' => $company->customers()->orderBy('name')->get(),
            'products' => $company->products()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $quote = DB::transaction(function () use ($data) {
            $company = auth()->user()->company;
            $this->ensureCustomerBelongsToCompany((int) $data['customer_id']);
            [$subtotal, $tax, $total] = $this->totals($data['items']);

            $next = (int) $company->quotes()->withTrashed()
                ->whereNull('parent_quote_id')
                ->selectRaw("MAX(CAST(SUBSTR(number, 3) AS INTEGER)) as max_number")
                ->value('max_number') + 1;

            $quote = Quote::create([
                'company_id' => $company->id,
                'customer_id' => $data['customer_id'],
                'number' => 'Q-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT),
                'title' => $data['title'],
                'valid_until' => $data['valid_until'],
                'notes' => $data['notes'] ?? null,
                'subtotal_cents' => $subtotal,
                'tax_cents' => $tax,
                'total_cents' => $total,
                'currency' => $company->currency,
                'approval_token' => (string) Str::uuid(),
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $this->replaceItems($quote, $data['items']);

            return $quote;
        });

        Audit::record('quote.created', $quote, ['total_cents' => $quote->total_cents]);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quotation created and ready to share.');
    }

    public function show(Quote $quote)
    {
        $this->ensureOwned($quote);
        $root = $quote->root();
        $versions = collect([$root])->merge($root->revisions()->get())->sortBy('version');

        return view('quotes.show', [
            'quote' => $quote->load('customer', 'company', 'items', 'invoice'),
            'versions' => $versions,
        ]);
    }

    public function revision(Quote $quote)
    {
        $this->ensureOwned($quote);
        abort_unless($quote->status === 'changes_requested', 422, 'Only quotations with requested changes can be revised.');

        $revision = DB::transaction(function () use ($quote) {
            $root = Quote::whereKey($quote->parent_quote_id ?: $quote->id)->lockForUpdate()->firstOrFail();
            $nextVersion = (int) Quote::where(function ($query) use ($root) {
                $query->whereKey($root->id)->orWhere('parent_quote_id', $root->id);
            })->max('version') + 1;

            $revision = $quote->replicate([
                'status', 'approval_token', 'sent_at', 'viewed_at', 'decided_at',
                'decision_name', 'decision_ip', 'decision_comment',
            ]);
            $revision->parent_quote_id = $root->id;
            $revision->number = $root->number.'-R'.$nextVersion;
            $revision->version = $nextVersion;
            $revision->status = 'draft';
            $revision->approval_token = (string) Str::uuid();
            $revision->sent_at = null;
            $revision->viewed_at = null;
            $revision->decided_at = null;
            $revision->save();

            foreach ($quote->items as $item) {
                $revision->items()->create($item->only([
                    'description', 'quantity', 'unit_price_cents', 'total_cents',
                ]));
            }

            return $revision;
        });

        Audit::record('quote.revision_created', $revision, ['source_quote_id' => $quote->id]);

        return redirect()->route('quotes.edit', $revision)
            ->with('success', 'Revision created. Update it, then send it to the customer.');
    }

    public function edit(Quote $quote)
    {
        $this->ensureOwned($quote);
        abort_unless($quote->status === 'draft', 422, 'Only draft quotations can be edited.');
        $company = auth()->user()->company;

        return view('quotes.edit', [
            'quote' => $quote->load('items'),
            'customers' => $company->customers()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Quote $quote)
    {
        $this->ensureOwned($quote);
        abort_unless($quote->status === 'draft', 422, 'Only draft quotations can be edited.');
        $data = $this->validated($request);
        $this->ensureCustomerBelongsToCompany((int) $data['customer_id']);

        DB::transaction(function () use ($quote, $data, $request) {
            [$subtotal, $tax, $total] = $this->totals($data['items']);
            $send = $request->input('action') === 'send';

            $quote->update([
                'customer_id' => $data['customer_id'],
                'title' => $data['title'],
                'valid_until' => $data['valid_until'],
                'notes' => $data['notes'] ?? null,
                'subtotal_cents' => $subtotal,
                'tax_cents' => $tax,
                'total_cents' => $total,
                'status' => $send ? 'sent' : 'draft',
                'sent_at' => $send ? now() : null,
            ]);

            $this->replaceItems($quote, $data['items']);
        });

        $sent = $request->input('action') === 'send';
        Audit::record($sent ? 'quote.revision_sent' : 'quote.revision_updated', $quote, [
            'version' => $quote->version,
            'total_cents' => $quote->total_cents,
        ]);

        return redirect()->route('quotes.show', $quote)
            ->with('success', $sent ? 'Revision sent with a new approval link.' : 'Draft revision saved.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'title' => 'required|max:150',
            'valid_until' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|max:2000',
            'items' => 'required|array|min:1|max:100',
            'items.*.description' => 'required|max:255',
            'items.*.quantity' => 'required|integer|min:1|max:999',
            'items.*.unit_price' => 'required|numeric|min:0|max:9999999',
        ]);
    }

    private function totals(array $items): array
    {
        $subtotal = collect($items)->sum(
            fn (array $item) => (int) round($item['unit_price'] * 100) * (int) $item['quantity']
        );
        $tax = (int) round($subtotal * (auth()->user()->company->tax_rate / 100));

        return [$subtotal, $tax, $subtotal + $tax];
    }

    private function replaceItems(Quote $quote, array $items): void
    {
        $quote->items()->delete();
        foreach ($items as $item) {
            $unit = (int) round($item['unit_price'] * 100);
            $quote->items()->create([
                'description' => $item['description'],
                'quantity' => (int) $item['quantity'],
                'unit_price_cents' => $unit,
                'total_cents' => $unit * (int) $item['quantity'],
            ]);
        }
    }

    private function ensureOwned(Quote $quote): void
    {
        abort_unless($quote->company_id === auth()->user()->company_id, 404);
    }

    private function ensureCustomerBelongsToCompany(int $customerId): void
    {
        abort_unless(auth()->user()->company->customers()->whereKey($customerId)->exists(), 422, 'Invalid customer.');
    }
}
