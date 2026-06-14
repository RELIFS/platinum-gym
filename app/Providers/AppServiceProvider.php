<?php

namespace App\Providers;

use App\Features\Gymmi\Clients\GeminiGymmiClient;
use App\Features\Gymmi\Contracts\GymmiAssistantClient;
use App\Features\Payments\Contracts\PaymentGateway;
use App\Features\Payments\Gateways\MidtransSnapGateway;
use App\Models\ClassEnrollment;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\QrToken;
use App\Policies\ClassEnrollmentPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MemberPolicy;
use App\Policies\MembershipPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\QrTokenPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, MidtransSnapGateway::class);
        $this->app->singleton(GymmiAssistantClient::class, GeminiGymmiClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Member::class, MemberPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Membership::class, MembershipPolicy::class);
        Gate::policy(ClassEnrollment::class, ClassEnrollmentPolicy::class);
        Gate::policy(QrToken::class, QrTokenPolicy::class);

        RateLimiter::for('gymmi', function (Request $request): Limit {
            $maxAttempts = (int) config('services.gemini.rate_limit_per_minute', 12);
            $key = $request->user()?->id
                ? 'user:'.$request->user()->id
                : 'ip:'.$request->ip();

            return Limit::perMinute($maxAttempts)->by($key);
        });
    }
}
