<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\PlatformCommission;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMvpTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $freelancer;
    protected User $unauthorizedUser;
    protected User $admin;
    protected Project $project;
    protected Proposal $proposal;
    protected Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create([
            'name' => 'العميل التجريبي',
            'email' => 'client.test@tasker.com',
            'account_type' => 'client',
            'is_admin' => false,
        ]);

        $this->freelancer = User::factory()->create([
            'name' => 'المستقل التجريبي',
            'email' => 'freelancer.test@tasker.com',
            'account_type' => 'freelancer',
            'is_admin' => false,
        ]);

        $this->unauthorizedUser = User::factory()->create([
            'name' => 'مستخدم آخر',
            'email' => 'other.user@tasker.com',
            'account_type' => 'client',
            'is_admin' => false,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'مدير النظام',
            'email' => 'admin.test@tasker.com',
            'account_type' => 'client',
            'is_admin' => true,
        ]);

        $this->project = Project::create([
            'owner_id' => $this->client->id,
            'title' => 'مشروع تطوير نظام تجريبي',
            'description' => 'وصف تفصيلي للمشروع التجريبي في البيئة المحلية.',
            'budget' => 500.00,
            'status' => 'open',
        ]);

        $this->proposal = Proposal::create([
            'project_id' => $this->project->id,
            'freelancer_id' => $this->freelancer->id,
            'cover_letter' => 'عرض عمل احترافي لتنفيذ المشروع على أكمل وجه.',
            'bid_amount' => 500.00,
            'delivery_days' => 5,
            'status' => 'accepted',
        ]);

        $this->contract = Contract::create([
            'project_id' => $this->project->id,
            'proposal_id' => $this->proposal->id,
            'client_id' => $this->client->id,
            'freelancer_id' => $this->freelancer->id,
            'title' => 'عقد اتفاقية: مشروع تطوير نظام تجريبي',
            'amount' => 500.00,
            'status' => 'active',
            'payment_status' => 'unpaid',
            'start_date' => now(),
        ]);
    }

    public function test_client_can_initiate_payment_for_own_contract(): void
    {
        $response = $this->actingAs($this->client)
            ->post(route('payments.initiate', $this->contract));

        $response->assertRedirect();
        
        $payment = Payment::where('contract_id', $this->contract->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(500.00, $payment->amount);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals($this->client->id, $payment->client_id);
    }

    public function test_unauthorized_user_cannot_initiate_payment_for_another_clients_contract(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)
            ->post(route('payments.initiate', $this->contract));

        $response->assertSessionHasErrors(['payment']);
        $this->assertDatabaseMissing('payments', ['contract_id' => $this->contract->id]);
    }

    public function test_payment_amount_comes_strictly_from_server_database(): void
    {
        // Try submitting malicious fake amount in payload
        $response = $this->actingAs($this->client)
            ->post(route('payments.initiate', $this->contract), [
                'amount' => 1.00, // Should be ignored
            ]);

        $payment = Payment::where('contract_id', $this->contract->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(500.00, $payment->amount);
    }

    public function test_fake_gateway_successful_payment_callback_and_commission_calculation(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->initiatePayment($this->client, $this->contract);

        $response = $this->actingAs($this->client)
            ->post(route('payments.callback', $payment), [
                'simulated_status' => 'paid',
            ]);

        $response->assertRedirect(route('contracts.show', $this->contract));

        $payment->refresh();
        $this->assertTrue($payment->isPaid());
        $this->assertEquals('paid', $this->contract->fresh()->payment_status);

        // Verify Server-Side Commission Calculation (10% rate)
        $commission = PlatformCommission::where('payment_id', $payment->id)->first();
        $this->assertNotNull($commission);
        $this->assertEquals(0.1000, $commission->rate);
        $this->assertEquals(500.00, $commission->gross_amount);
        $this->assertEquals(50.00, $commission->commission_amount);
        $this->assertEquals(450.00, $commission->freelancer_amount);
    }

    public function test_fake_gateway_failed_payment_callback(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->initiatePayment($this->client, $this->contract);

        $response = $this->actingAs($this->client)
            ->post(route('payments.callback', $payment), [
                'simulated_status' => 'failed',
            ]);

        $payment->refresh();
        $this->assertTrue($payment->isFailed());
        $this->assertEquals('failed', $this->contract->fresh()->payment_status);
        $this->assertDatabaseMissing('platform_commissions', ['payment_id' => $payment->id]);
    }

    public function test_duplicate_callback_is_idempotent_and_does_not_duplicate_commissions(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->initiatePayment($this->client, $this->contract);

        // First callback
        $service->processPaymentCallback($payment, 'paid');
        $this->assertEquals(1, PlatformCommission::where('payment_id', $payment->id)->count());

        // Second duplicate callback
        $service->processPaymentCallback($payment, 'paid');
        $this->assertEquals(1, PlatformCommission::where('payment_id', $payment->id)->count());
    }

    public function test_refund_processing_for_paid_payment(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->initiatePayment($this->client, $this->contract);
        $service->processPaymentCallback($payment, 'paid');

        // Admin refunds payment
        $refundedPayment = $service->refundPayment($this->admin, $payment, 500.00, 'Test Refund');

        $this->assertTrue($refundedPayment->isRefunded());
        $this->assertEquals('refunded', $this->contract->fresh()->payment_status);
        $this->assertEquals('refunded', $refundedPayment->commission->fresh()->status);
    }

    public function test_unauthorized_user_cannot_refund_payment(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->initiatePayment($this->client, $this->contract);
        $service->processPaymentCallback($payment, 'paid');

        $this->expectException(\InvalidArgumentException::class);
        $service->refundPayment($this->unauthorizedUser, $payment);
    }

    public function test_freelancer_can_view_contract_page_with_net_payout_amount(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->initiatePayment($this->client, $this->contract);
        $service->processPaymentCallback($payment, 'paid');

        $response = $this->actingAs($this->freelancer)
            ->get(route('contracts.show', $this->contract));

        $response->assertStatus(200);
        $response->assertSee('المبلغ المخصص للمستقل لهذا العقد');
        $response->assertSee('450.00');
    }

    public function test_admin_can_access_payments_overview_page(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->initiatePayment($this->client, $this->contract);
        $service->processPaymentCallback($payment, 'paid');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payments'));

        $response->assertStatus(200);
        $response->assertSee('تدقيق وسجل المدفوعات والعمولات');
        $response->assertSee('500.00');
        $response->assertSee('50.00');
    }

    public function test_legal_policy_pages_are_accessible(): void
    {
        $this->get(route('legal.terms'))->assertStatus(200)->assertSee('شروط وأحكام');
        $this->get(route('legal.privacy'))->assertStatus(200)->assertSee('سياسة الخصوصية');
        $this->get(route('legal.payment-policy'))->assertStatus(200)->assertSee('سياسة المدفوعات');
        $this->get(route('legal.refund-policy'))->assertStatus(200)->assertSee('سياسة الاسترداد');
        $this->get(route('legal.marketplace-rules'))->assertStatus(200)->assertSee('قواعد وإرشادات سوق العمل');
    }
}
