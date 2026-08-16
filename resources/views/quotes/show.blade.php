@extends('layout', ['title' => $quote->number . ' · Quotely'])

@section('content')
    <div class="top">
        <div>
            <span class="badge">{{ str_replace('_', ' ', $quote->status) }}</span>
            <h1 style="margin-top: 9px">{{ $quote->number }}</h1>
            <p>{{ $quote->title }} · {{ $quote->customer->name }} · Version {{ $quote->version }}</p>
        </div>

        <div class="actions">
            @if($quote->status !== 'draft')
                <a class="btn secondary" target="_blank" href="{{ route('approval.show', $quote->approval_token) }}">
                    Open customer link
                </a>
            @endif

            @if($quote->status === 'draft')
                <a class="btn" href="{{ route('quotes.edit', $quote) }}">Edit revision</a>
            @endif

            @if($quote->status === 'changes_requested')
                <form method="POST" action="{{ route('quotes.revision', $quote) }}">
                    @csrf
                    <button class="btn" type="submit">Create revision</button>
                </form>
            @endif

            @if($quote->status === 'accepted' && !$quote->invoice)
                <form method="POST" action="{{ route('invoices.store', $quote) }}">
                    @csrf
                    <button class="btn" type="submit">Convert to invoice</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="quote-head">
            <div>
                <strong>{{ $quote->company->name }}</strong>
                <p>{{ $quote->company->email }}</p>
            </div>
            <div>
                <strong>Prepared for</strong>
                <p>
                    {{ $quote->customer->name }}<br>
                    {{ $quote->customer->email }}<br>
                    Valid until {{ $quote->valid_until->format('M j, Y') }}
                </p>
            </div>
        </div>

        <table>
            <thead><tr><th>Description</th><th>Qty</th><th>Unit price</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($quote->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $quote->money($item->unit_price_cents) }}</td>
                        <td>{{ $quote->money($item->total_cents) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span>Subtotal</span><span>{{ $quote->money($quote->subtotal_cents) }}</span></div>
            <div>
                <span>{{ $quote->company->tax_name }} ({{ $quote->company->tax_rate }}%)</span>
                <span>{{ $quote->money($quote->tax_cents) }}</span>
            </div>
            <div class="grand"><span>Total</span><span>{{ $quote->money($quote->total_cents) }}</span></div>
        </div>

        @if($quote->notes)
            <p><strong>Notes</strong><br>{{ $quote->notes }}</p>
        @endif
    </div>

    @if($quote->decided_at)
        <div class="card" style="margin-top: 18px">
            <strong>{{ $quote->status === 'changes_requested' ? 'Customer requested changes' : 'Decision recorded' }}</strong>
            <p>By {{ $quote->decision_name }} on {{ $quote->decided_at->format('M j, Y g:i A') }}</p>

            @if($quote->decision_comment)
                <div class="alert">{{ $quote->decision_comment }}</div>
            @endif
        </div>
    @endif

    @if($quote->invoice)
        <div class="alert" style="margin-top: 18px">
            Invoice <strong>{{ $quote->invoice->number }}</strong> exists and is {{ $quote->invoice->status }}.
        </div>
    @endif

    @if($versions->count() > 1)
        <div class="card" style="margin-top: 18px">
            <h2>Version history</h2>
            <table>
                <thead><tr><th>Version</th><th>Number</th><th>Status</th><th>Decision</th><th></th></tr></thead>
                <tbody>
                    @foreach($versions as $version)
                        <tr>
                            <td>Version {{ $version->version }}</td>
                            <td>{{ $version->number }}</td>
                            <td><span class="badge">{{ str_replace('_', ' ', $version->status) }}</span></td>
                            <td>{{ $version->decided_at?->format('M j, Y') ?? '—' }}</td>
                            <td><a href="{{ route('quotes.show', $version) }}">Open →</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
