<?php

namespace Tests\Feature;

use App\Models\{Company, Customer, Quote, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuoteRevisionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function requestedQuote(): Quote
    {
        $company = Company::create([
            'name' => 'Studio',
            'email' => 'studio@test.com',
            'currency' => 'USD',
            'tax_rate' => 16,
            'trial_ends_at' => now()->addWeek(),
        ]);
        $this->actingAs(User::factory()->create(['company_id' => $company->id, 'role' => 'owner']));
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'Client', 'email' => 'client@test.com']);
        $quote = Quote::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'number' => 'Q-0001',
            'title' => 'Original project',
            'status' => 'changes_requested',
            'valid_until' => now()->addWeek(),
            'subtotal_cents' => 10000,
            'tax_cents' => 1600,
            'total_cents' => 11600,
            'currency' => 'USD',
            'approval_token' => (string) Str::uuid(),
            'decision_name' => 'Client',
            'decision_comment' => 'Please change the scope.',
            'decided_at' => now(),
        ]);
        $quote->items()->create([
            'description' => 'Original service',
            'quantity' => 1,
            'unit_price_cents' => 10000,
            'total_cents' => 10000,
        ]);

        return $quote;
    }

    public function test_requested_quote_can_be_copied_to_a_draft_revision(): void
    {
        $original = $this->requestedQuote();

        $this->post(route('quotes.revision', $original))->assertRedirect();

        $revision = Quote::where('parent_quote_id', $original->id)->firstOrFail();
        $this->assertSame('Q-0001-R2', $revision->number);
        $this->assertSame(2, $revision->version);
        $this->assertSame('draft', $revision->status);
        $this->assertSame('Original service', $revision->items()->first()->description);
        $this->assertSame('changes_requested', $original->fresh()->status);
    }

    public function test_draft_revision_can_be_edited_recalculated_and_sent(): void
    {
        $original = $this->requestedQuote();
        $this->post(route('quotes.revision', $original));
        $revision = Quote::where('parent_quote_id', $original->id)->firstOrFail();

        $this->put(route('quotes.update', $revision), [
            'customer_id' => $revision->customer_id,
            'title' => 'Revised project',
            'valid_until' => now()->addDays(10)->format('Y-m-d'),
            'notes' => 'Updated terms',
            'items' => [[
                'description' => 'Revised service',
                'quantity' => 2,
                'unit_price' => 75,
            ]],
            'action' => 'send',
        ])->assertRedirect(route('quotes.show', $revision));

        $revision->refresh();
        $this->assertSame('sent', $revision->status);
        $this->assertSame(15000, $revision->subtotal_cents);
        $this->assertSame(2400, $revision->tax_cents);
        $this->assertSame(17400, $revision->total_cents);
        $this->assertNotNull($revision->sent_at);
        $this->assertSame('Original project', $original->fresh()->title);
    }

    public function test_draft_revision_is_not_publicly_visible(): void
    {
        $original = $this->requestedQuote();
        $this->post(route('quotes.revision', $original));
        $revision = Quote::where('parent_quote_id', $original->id)->firstOrFail();

        $this->get(route('approval.show', $revision->approval_token))->assertNotFound();
    }
}
