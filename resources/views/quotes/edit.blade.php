@extends('layout', ['title' => 'Edit ' . $quote->number . ' · Quotely'])

@section('content')
    <div class="top">
        <div>
            <span class="badge">Draft revision</span>
            <h1 style="margin-top: 9px">Edit {{ $quote->number }}</h1>
            <p>Version {{ $quote->version }} remains private until you send it.</p>
        </div>
        <a class="btn secondary" href="{{ route('quotes.show', $quote) }}">Cancel</a>
    </div>

    <form class="card" method="POST" action="{{ route('quotes.update', $quote) }}">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="alert" style="background:#f8dfdc;color:#842e28">{{ $errors->first() }}</div>
        @endif

        <div class="grid" style="grid-template-columns: 1fr 1fr">
            <div>
                <label>Customer</label>
                <select name="customer_id" required>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $quote->customer_id) == $customer->id)>
                            {{ $customer->name }}{{ $customer->company_name ? ' — '.$customer->company_name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Valid until</label>
                <input type="date" name="valid_until" value="{{ old('valid_until', $quote->valid_until->format('Y-m-d')) }}" required>
            </div>
        </div>

        <label>Project title</label>
        <input name="title" value="{{ old('title', $quote->title) }}" required>

        <h2 style="margin-top: 28px">Line items</h2>
        @foreach(old('items', $quote->items->map(fn($item) => [
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price_cents / 100,
        ])->all()) as $index => $item)
            <div class="row">
                <div>
                    <label>Description</label>
                    <input name="items[{{ $index }}][description]" value="{{ $item['description'] }}" required>
                </div>
                <div>
                    <label>Quantity</label>
                    <input type="number" min="1" max="999" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" required>
                </div>
                <div>
                    <label>Unit price</label>
                    <input type="number" min="0" step="0.01" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}" required>
                </div>
            </div>
        @endforeach

        <label>Notes and terms</label>
        <textarea name="notes">{{ old('notes', $quote->notes) }}</textarea>

        <div class="actions" style="margin-top: 22px">
            <button class="btn secondary" type="submit" name="action" value="save">Save draft</button>
            <button class="btn" type="submit" name="action" value="send">Save and send revision</button>
        </div>
    </form>
@endsection
