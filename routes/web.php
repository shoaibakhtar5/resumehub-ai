<?php

use App\Http\Controllers\Admin\AdminResourceController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AtsController;
use App\Http\Controllers\FrontendPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendPageController::class, 'home'])->name('home');
Route::get('/about', [FrontendPageController::class, 'about'])->name('about');
Route::get('/features', [FrontendPageController::class, 'features'])->name('features');
Route::get('/pricing', [FrontendPageController::class, 'pricing'])->name('pricing');
Route::get('/contact', [FrontendPageController::class, 'contact'])->name('contact');
Route::post('/contact', [FrontendPageController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/blog', [FrontendPageController::class, 'blogIndex'])->name('blog.index');
Route::get('/blog/{slug}', [FrontendPageController::class, 'blogShow'])->name('blog.show');
Route::get('/faq', [FrontendPageController::class, 'faq'])->name('faq');
Route::get('/terms', [FrontendPageController::class, 'terms'])->name('terms');
Route::get('/privacy', [FrontendPageController::class, 'privacy'])->name('privacy');
Route::get('/share/resume/{token}', [ResumeController::class, 'shared'])->name('resume.shared');

Route::middleware('guest')->group(function () {
    Route::get('/two-factor-challenge', [FrontendPageController::class, 'twoFactor'])->name('two-factor.challenge');
});

Route::get('/dashboard', [FrontendPageController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/resumes', [ResumeController::class, 'index'])->name('resumes.index');
    Route::post('/resumes', [ResumeController::class, 'store'])->name('resumes.store');
    Route::post('/resumes/import', [ResumeController::class, 'import'])->name('resumes.import');
    Route::get('/resumes/create', [ResumeController::class, 'create'])->name('resumes.create');
    Route::get('/resumes/{resume}', [ResumeController::class, 'show'])->name('resumes.show');
    Route::get('/resumes/{resume}/edit', [ResumeController::class, 'edit'])->name('resumes.edit');
    Route::patch('/resumes/{resume}', [ResumeController::class, 'update'])->name('resumes.update');
    Route::delete('/resumes/{resume}', [ResumeController::class, 'destroy'])->name('resumes.destroy');
    Route::post('/resumes/{resume}/autosave', [ResumeController::class, 'autosave'])->name('resumes.autosave');
    Route::post('/resumes/{resume}/duplicate', [ResumeController::class, 'duplicate'])->name('resumes.duplicate');
    Route::post('/resumes/{resume}/favorite', [ResumeController::class, 'favorite'])->name('resumes.favorite');
    Route::post('/resumes/{resume}/archive', [ResumeController::class, 'archive'])->name('resumes.archive');
    Route::post('/resumes/{resume}/restore', [ResumeController::class, 'restore'])->name('resumes.restore');
    Route::post('/resumes/{resume}/versions/{version}/restore', [ResumeController::class, 'restoreVersion'])->name('resumes.versions.restore');
    Route::post('/resumes/{resume}/share', [ResumeController::class, 'share'])->name('resumes.share');
    Route::post('/resumes/{resume}/download', [ResumeController::class, 'download'])->name('resumes.download');
    Route::get('/resume-builder', [ResumeController::class, 'create'])->name('resume.builder');
    Route::get('/resume-preview/{resume?}', [ResumeController::class, 'preview'])->name('resume.preview');
    Route::get('/resume-templates', [TemplateController::class, 'index'])->name('resume.templates');
    Route::post('/resume-templates/{template}/apply', [TemplateController::class, 'apply'])->name('resume.templates.apply');
    Route::get('/downloads', [ResumeController::class, 'library'])->defaults('page', 'downloads')->name('downloads');
    Route::get('/shared-resumes', [ResumeController::class, 'library'])->defaults('page', 'shared-resumes')->name('shared-resumes');
    Route::get('/favorite-resumes', [ResumeController::class, 'library'])->defaults('page', 'favorite-resumes')->name('favorite-resumes');
    Route::get('/archived-resumes', [ResumeController::class, 'library'])->defaults('page', 'archived-resumes')->name('archived-resumes');
    Route::get('/version-history', [ResumeController::class, 'library'])->defaults('page', 'version-history')->name('version-history');
    Route::get('/settings', [FrontendPageController::class, 'userPage'])->defaults('page', 'settings')->name('settings');
    Route::get('/notifications', [FrontendPageController::class, 'userPage'])->defaults('page', 'notifications')->name('notifications');
    Route::get('/ai-resume-studio', [AiController::class, 'studio'])->name('ai.studio');
    Route::post('/ai/generate', [AiController::class, 'generate'])->name('ai.generate');
    Route::get('/ats-checker', [AtsController::class, 'index'])->name('ats.checker');
    Route::post('/ats-checker', [AtsController::class, 'store'])->name('ats.reports.store');
    Route::get('/ats-reports/{report}', [AtsController::class, 'show'])->name('ats.reports.show');
    Route::get('/resume-review', [AiController::class, 'tool'])->defaults('page', 'resume-review')->name('resume.review');
    Route::get('/resume-score', [AiController::class, 'tool'])->defaults('page', 'resume-score')->name('resume.score');
    Route::get('/cover-letter-generator', [AiController::class, 'tool'])->defaults('page', 'cover-letter-generator')->name('cover-letter');
    Route::get('/interview-questions', [AiController::class, 'tool'])->defaults('page', 'interview-questions')->name('interview.questions');
    Route::get('/keyword-optimizer', [AiController::class, 'tool'])->defaults('page', 'keyword-optimizer')->name('keyword.optimizer');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminResourceController::class, 'dashboard'])->name('dashboard');
        Route::get('/system-status', [AdminResourceController::class, 'dashboard'])->name('system-status');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles');
        Route::get('/roles/create', [AdminRoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}', [AdminRoleController::class, 'show'])->name('roles.show');
        Route::get('/roles/{role}/edit', [AdminRoleController::class, 'edit'])->name('roles.edit');
        Route::patch('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('/permissions', [AdminPermissionController::class, 'index'])->name('permissions');
        Route::get('/permissions/create', [AdminPermissionController::class, 'create'])->name('permissions.create');
        Route::post('/permissions', [AdminPermissionController::class, 'store'])->name('permissions.store');
        Route::get('/permissions/{permission}', [AdminPermissionController::class, 'show'])->name('permissions.show');
        Route::get('/permissions/{permission}/edit', [AdminPermissionController::class, 'edit'])->name('permissions.edit');
        Route::patch('/permissions/{permission}', [AdminPermissionController::class, 'update'])->name('permissions.update');
        Route::delete('/permissions/{permission}', [AdminPermissionController::class, 'destroy'])->name('permissions.destroy');

        Route::get('/templates', [AdminTemplateController::class, 'index'])->name('templates');
        Route::get('/templates/create', [AdminTemplateController::class, 'create'])->name('templates.create');
        Route::post('/templates', [AdminTemplateController::class, 'store'])->name('templates.store');
        Route::get('/templates/{template}/preview', [AdminTemplateController::class, 'preview'])->name('templates.preview');
        Route::get('/templates/{template}/edit', [AdminTemplateController::class, 'edit'])->name('templates.edit');
        Route::patch('/templates/{template}', [AdminTemplateController::class, 'update'])->name('templates.update');
        Route::delete('/templates/{template}', [AdminTemplateController::class, 'destroy'])->name('templates.destroy');
        Route::post('/templates/{template}/duplicate', [AdminTemplateController::class, 'duplicate'])->name('templates.duplicate');
        Route::patch('/templates/{template}/status', [AdminTemplateController::class, 'status'])->name('templates.status');
        Route::patch('/templates/{template}/featured', [AdminTemplateController::class, 'featured'])->name('templates.featured');

        foreach ([
            'resumes' => 'resumes',
            'blog' => 'blog',
            'pages' => 'pages',
            'team' => 'team',
            'ai-usage' => 'ai-usage',
            'plans' => 'plans',
            'subscriptions' => 'subscriptions',
            'transactions' => 'transactions',
            'coupons' => 'coupons',
            'settings' => 'settings',
            'email-templates' => 'email-templates',
            'notifications' => 'notifications',
            'logs' => 'logs',
        ] as $uri => $resource) {
            Route::get('/'.$uri, [AdminResourceController::class, 'index'])
                ->defaults('resource', $resource)
                ->name($resource);
        }

        Route::get('/templates/upload', fn () => redirect()->route('admin.templates.create'))->name('template-upload');
        Route::get('/website-settings', [AdminResourceController::class, 'index'])
            ->defaults('resource', 'settings')
            ->name('website-settings');

        Route::get('/resources/{resource}/create', [AdminResourceController::class, 'create'])->name('resources.create');
        Route::get('/resources/{resource}/{id}/edit', [AdminResourceController::class, 'edit'])->name('resources.edit');
        Route::get('/resources/{resource}/{id}', [AdminResourceController::class, 'show'])->name('resources.show');
        Route::post('/resources/{resource}', [AdminResourceController::class, 'store'])->name('resources.store');
        Route::patch('/resources/{resource}/{id}', [AdminResourceController::class, 'update'])->name('resources.update');
        Route::delete('/resources/{resource}/{id}', [AdminResourceController::class, 'destroy'])->name('resources.destroy');
        Route::post('/resources/{resource}/bulk', [AdminResourceController::class, 'bulk'])->name('resources.bulk');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::fallback([FrontendPageController::class, 'notFound']);
