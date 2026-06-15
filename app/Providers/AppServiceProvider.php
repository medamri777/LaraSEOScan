<?php

namespace App\Providers;

use App\Models\Project;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share current project + all user projects with all views (for sidebar selector)
        View::composer('*', function ($view) {
            if (!Auth::check()) {
                return;
            }

            $user = Auth::user();
            if (!$user->tenant_id) {
                return;
            }

            $projects = Project::where('tenant_id', $user->tenant_id)->get(['id', 'name', 'url']);

            $currentProject = null;
            $currentId = session('current_project_id');
            if ($currentId) {
                $currentProject = $projects->firstWhere('id', $currentId);
            }
            // Auto-select first project if none selected
            if (!$currentProject && $projects->isNotEmpty()) {
                $currentProject = $projects->first();
                session(['current_project_id' => $currentProject->id]);
            }

            $view->with('currentProject', $currentProject);
            $view->with('userProjects', $projects);
        });

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return url()->route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        VerifyEmail::createUrlUsing(function ($notifiable) {
            return URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id'   => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });
    }
}
