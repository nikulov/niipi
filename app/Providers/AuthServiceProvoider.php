<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Footer;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\FormSubmissionFile;
use App\Models\GlobalSetting;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\FooterPolicy;
use App\Policies\FormFieldPolicy;
use App\Policies\FormPolicy;
use App\Policies\FormSubmissionFilePolicy;
use App\Policies\FormSubmissionPolicy;
use App\Policies\GlobalSettingPolicy;
use App\Policies\MenuPolicy;
use App\Policies\PagePolicy;
use App\Policies\PostPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvoider extends ServiceProvider
{
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Footer::class => FooterPolicy::class,
        Form::class => FormPolicy::class,
        FormField::class => FormFieldPolicy::class,
        FormSubmission::class => FormSubmissionPolicy::class,
        FormSubmissionFile::class  => FormSubmissionFilePolicy::class,
        GlobalSetting::class => GlobalSettingPolicy::class,
        Menu::class => MenuPolicy::class,
        Page::class => PagePolicy::class,
        Post::class => PostPolicy::class,
        Project::class => ProjectPolicy::class,
        User::class  => UserPolicy::class,
        
    ];
    
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
