<?php

use App\Http\Controllers\Admin\AdminResourceController;
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

        foreach ([
            'users' => 'users',
            'resumes' => 'resumes',
            'templates' => 'templates',
            'templates/upload' => 'template-upload',
            'blog' => 'blog',
            'categories' => 'categories',
            'tags' => 'tags',
            'team' => 'team',
            'website-settings' => 'website-settings',
            'seo-settings' => 'seo-settings',
            'ai-settings' => 'ai-settings',
            'analytics' => 'analytics',
            'contact-messages' => 'contact-messages',
            'media-library' => 'media-library',
            'roles' => 'roles',
            'permissions' => 'permissions',
            'logs' => 'logs',
        ] as $uri => $resource) {
            Route::get('/'.$uri, [AdminResourceController::class, 'index'])
                ->defaults('resource', $resource)
                ->name($resource);
        }

        Route::post('/resources/{resource}', [AdminResourceController::class, 'store'])->name('resources.store');
        Route::patch('/resources/{resource}/{id}', [AdminResourceController::class, 'update'])->name('resources.update');
        Route::delete('/resources/{resource}/{id}', [AdminResourceController::class, 'destroy'])->name('resources.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::fallback([FrontendPageController::class, 'notFound']);
