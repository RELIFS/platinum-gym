<?php

namespace App\Providers;

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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
    }
}
