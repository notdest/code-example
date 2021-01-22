<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if ($user->isBlocked()) {
                return false;
            }

            if ($user->isAdmin()) {
                return true;
            }
        });

        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('post-viewer', function ($user) {
            return $user->postsEnabled();
        });

        Gate::define('post-editor', function ($user) {
            return ($user->postsEnabled() && $user->isEditor());
        });

        Gate::define('article-viewer', function ($user) {
            return $user->articlesEnabled();
        });

        Gate::define('article-editor', function ($user) {
            return ($user->articlesEnabled() && $user->isEditor());
        });
    }
}
