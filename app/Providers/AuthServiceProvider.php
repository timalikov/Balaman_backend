<?php

namespace App\Providers;
use Laravel\Passport\Passport;
use Laravel\Passport\Client;
use Laravel\Passport\Token;
use Laravel\Passport\AuthCode;
use Laravel\Passport\PersonalAccessClient;


// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
        $this->registerPolicies();
        Passport::useTokenModel(Token::class); 
        Passport::useClientModel(Client::class); 
        Passport::useAuthCodeModel(AuthCode::class); 
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);


    }
}
