<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Admin\CanonicalAdmissionController;
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExtensionController;
use App\Http\Controllers\Admin\IdentityConflictReviewController;
use App\Http\Controllers\Admin\MusicBrainzArtistImportController as AdminMusicBrainzArtistImportController;
use App\Http\Controllers\Admin\MusicBrainzRecordingImportController as AdminMusicBrainzRecordingImportController;
use App\Http\Controllers\Admin\MusicBrainzReleaseImportController as AdminMusicBrainzReleaseImportController;
use App\Http\Controllers\Admin\MusicBrainzWorkImportController as AdminMusicBrainzWorkImportController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\Admin\PrivilegedAuditController;
use App\Http\Controllers\Admin\ProviderConfigurationController;
use App\Http\Controllers\Admin\ProviderImportDiscoveryController;
use App\Http\Controllers\Admin\ProviderImportPlanController;
use App\Http\Controllers\Admin\ProviderImportPreviewController;
use App\Http\Controllers\Admin\ProviderMutationController;
use App\Http\Controllers\Admin\YouTubeDestinationController;
use App\Http\Controllers\DesignLabController;
use App\Http\Controllers\Development\MusicBrainzArtistImportController;
use App\Http\Controllers\Development\StatusController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicCatalog\BrowseController;
use App\Http\Controllers\PublicCatalog\RobotsController;
use App\Http\Controllers\PublicCatalog\SitemapController;
use App\Http\Controllers\Search\EntityController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\ShellPreviewController;
use App\Http\Controllers\UiPreviewController;
use App\Support\DomainContracts\DomainContractRegistry;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/search', SearchController::class)->name('search');
Route::get('/artists', [BrowseController::class, 'artists'])->name('artists.index');
Route::get('/groups', [BrowseController::class, 'groups'])->name('groups.index');
Route::get('/releases', [BrowseController::class, 'releases'])->name('releases.index');
Route::get('/recordings', [BrowseController::class, 'recordings'])->name('recordings.index');
Route::get('/works', [BrowseController::class, 'works'])->name('works.index');
Route::get('/collections', [BrowseController::class, 'collections'])->name('collections.index');
Route::get('/artists/{slug}', [EntityController::class, 'artist'])->where('slug', '[a-z0-9-]+')->name('artists.show');
Route::get('/groups/{slug}', [EntityController::class, 'group'])->where('slug', '[a-z0-9-]+')->name('groups.show');
Route::get('/release-groups/{slug}', [EntityController::class, 'releaseGroup'])->where('slug', '[a-z0-9-]+')->name('release-groups.show');
Route::get('/releases/{slug}', [EntityController::class, 'release'])->where('slug', '[a-z0-9-]+')->name('releases.show');
Route::get('/recordings/{slug}', [EntityController::class, 'recording'])->where('slug', '[a-z0-9-]+')->name('recordings.show');
Route::get('/works/{slug}', [EntityController::class, 'work'])->where('slug', '[a-z0-9-]+')->name('works.show');
Route::get('/versions/{slug}', [EntityController::class, 'version'])->where('slug', '[a-z0-9-]+')->name('versions.show');
Route::get('/collections/{slug}', [EntityController::class, 'collection'])->where('slug', '[a-z0-9-]+')->name('collections.show');

Route::middleware(['auth', 'active', 'verified'])
    ->prefix('account')
    ->name('account.')
    ->group(function (): void {
        Route::get('/', [AccountController::class, 'overview'])->name('overview');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::get('/security', [AccountController::class, 'security'])->name('security');
    });

Route::middleware(['auth', 'active', 'verified', 'can:access-admin', 'two-factor.confirmed'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/catalog', [OperationsController::class, 'catalog'])->name('catalog.index');
        Route::get('/catalog/{type}', [CatalogController::class, 'index'])->where('type', EntityType::routePattern())->name('catalog.entities.index');
        Route::get('/catalog/{type}/{id}', [CatalogController::class, 'show'])->where(['type' => EntityType::routePattern(), 'id' => app(DomainContractRegistry::class)->adminUlidPattern()])->name('catalog.entities.show');
        Route::patch('/catalog/artist/{id}', [CatalogController::class, 'updateArtist'])->where('id', app(DomainContractRegistry::class)->adminUlidPattern())->name('catalog.artists.update')->middleware(['can:manage-catalog', 'password.confirm']);
        Route::get('/canonical-admissions', [CanonicalAdmissionController::class, 'index'])->name('canonical-admissions.index');
        Route::get('/canonical-admissions/{admission}', [CanonicalAdmissionController::class, 'show'])->where('admission', app(DomainContractRegistry::class)->adminUlidPattern())->name('canonical-admissions.show');
        Route::post('/canonical-admissions/assertions/{assertion}', [CanonicalAdmissionController::class, 'stage'])->where('assertion', app(DomainContractRegistry::class)->adminUlidPattern())->name('canonical-admissions.stage')->middleware(['can:manage-catalog', 'password.confirm']);
        Route::post('/canonical-admissions/{admission}/decisions', [CanonicalAdmissionController::class, 'decide'])->where('admission', app(DomainContractRegistry::class)->adminUlidPattern())->name('canonical-admissions.decide')->middleware(['can:manage-catalog', 'password.confirm']);
        Route::get('/providers', [OperationsController::class, 'providers'])->name('providers.index');
        Route::get('/providers/{provider}', [OperationsController::class, 'provider'])->where('provider', app(DomainContractRegistry::class)->adminUlidPattern())->name('providers.show');
        Route::post('/providers/{provider}/configuration', ProviderConfigurationController::class)->where('provider', app(DomainContractRegistry::class)->adminUlidPattern())->name('providers.configuration.update')->middleware(['can:manage-providers', 'password.confirm']);
        Route::post('/providers/{provider}/operations', [ProviderMutationController::class, 'provider'])->where('provider', app(DomainContractRegistry::class)->adminUlidPattern())->name('providers.mutate')->middleware(['can:manage-providers', 'password.confirm']);
        Route::post('/providers/{provider}/musicbrainz/artists/import', AdminMusicBrainzArtistImportController::class)->where('provider', app(DomainContractRegistry::class)->adminUlidPattern())->name('providers.musicbrainz.artists.import')->middleware(['can:manage-providers', 'password.confirm']);
        Route::post('/providers/{provider}/musicbrainz/releases/import', AdminMusicBrainzReleaseImportController::class)->where('provider', app(DomainContractRegistry::class)->adminUlidPattern())->name('providers.musicbrainz.releases.import')->middleware(['can:manage-providers', 'password.confirm']);
        Route::post('/providers/{provider}/musicbrainz/recordings/import', AdminMusicBrainzRecordingImportController::class)->where('provider', app(DomainContractRegistry::class)->adminUlidPattern())->name('providers.musicbrainz.recordings.import')->middleware(['can:manage-providers', 'password.confirm']);
        Route::post('/providers/{provider}/musicbrainz/works/import', AdminMusicBrainzWorkImportController::class)->where('provider', app(DomainContractRegistry::class)->adminUlidPattern())->name('providers.musicbrainz.works.import')->middleware(['can:manage-providers', 'password.confirm']);
        Route::post('/providers/{provider}/youtube/destinations/approve', YouTubeDestinationController::class)->where('provider', app(DomainContractRegistry::class)->adminUlidPattern())->name('providers.youtube.destinations.approve')->middleware(['can:manage-providers', 'password.confirm']);
        Route::get('/imports', [OperationsController::class, 'imports'])->name('imports.index');
        Route::get('/imports/preview', [ProviderImportPreviewController::class, 'index'])->name('imports.preview');
        Route::post('/imports/search', [ProviderImportDiscoveryController::class, 'search'])->name('imports.search');
        Route::post('/imports/select', [ProviderImportDiscoveryController::class, 'select'])->name('imports.select');
        Route::post('/imports/preview', [ProviderImportPreviewController::class, 'preview'])->name('imports.preview.build');
        Route::post('/imports/preview/execute', [ProviderImportPlanController::class, 'execute'])->name('imports.preview.execute')->middleware(['can:manage-providers', 'password.confirm']);
        Route::get('/imports/{run}', [OperationsController::class, 'importRun'])->where('run', app(DomainContractRegistry::class)->adminUlidPattern())->name('imports.show');
        Route::post('/imports/{run}/operations', [ProviderMutationController::class, 'import'])->where('run', app(DomainContractRegistry::class)->adminUlidPattern())->name('imports.recover')->middleware(['can:manage-providers', 'password.confirm']);
        Route::get('/quarantine', [OperationsController::class, 'quarantine'])->name('quarantine.index');
        Route::get('/identity-conflicts', [IdentityConflictReviewController::class, 'index'])->name('identity-conflicts.index');
        Route::get('/identity-conflicts/{review}', [IdentityConflictReviewController::class, 'show'])->where('review', app(DomainContractRegistry::class)->adminUlidPattern())->name('identity-conflicts.show');
        Route::post('/identity-conflicts/{review}/decisions', [IdentityConflictReviewController::class, 'decide'])->where('review', app(DomainContractRegistry::class)->adminUlidPattern())->name('identity-conflicts.decide')->middleware(['can:manage-identity-conflicts', 'password.confirm']);
        Route::get('/users', [OperationsController::class, 'users'])->name('users.index');
        Route::get('/system', [OperationsController::class, 'system'])->name('system.index');
        Route::get('/audit', PrivilegedAuditController::class)->name('audit.index')->middleware('can:view-audit');
        Route::get('/extensions', [ExtensionController::class, 'index'])->name('extensions.index');
        Route::post('/extensions/upload', [ExtensionController::class, 'upload'])->name('extensions.upload')->middleware(['can:manage-extensions', 'password.confirm']);
        Route::post('/extensions/install', [ExtensionController::class, 'install'])->name('extensions.install')->middleware(['can:manage-extensions', 'password.confirm']);
        Route::get('/extensions/{extension}', [ExtensionController::class, 'show'])->name('extensions.show');
        Route::post('/extensions/{extension}/upgrade', [ExtensionController::class, 'upgrade'])->name('extensions.upgrade')->middleware(['can:manage-extensions', 'password.confirm']);
        Route::post('/extensions/{extension}/toggle', [ExtensionController::class, 'toggle'])->name('extensions.toggle')->middleware(['can:manage-extensions', 'password.confirm']);
        Route::post('/extensions/{extension}/activate', [ExtensionController::class, 'activate'])->name('extensions.activate')->middleware(['can:manage-extensions', 'password.confirm']);
        Route::post('/extensions/{extension}/rollback', [ExtensionController::class, 'rollback'])->name('extensions.rollback')->middleware(['can:manage-extensions', 'password.confirm']);
        Route::post('/extensions/{extension}/cleanup', [ExtensionController::class, 'cleanup'])->name('extensions.cleanup')->middleware(['can:manage-extensions', 'password.confirm']);
    });

if (app()->environment(['local', 'testing'])) {
    Route::get('/development/status', StatusController::class)->name('development.status');
    Route::post('/development/providers/musicbrainz/artists/import', MusicBrainzArtistImportController::class)->name('development.musicbrainz.artists.import');
}

if (app()->environment(['local', 'testing']) || config('design-lab.enabled')) {
    Route::prefix('development/design-system')->name('development.design-system.')->group(function (): void {
        Route::get('/shell/frontend', [ShellPreviewController::class, 'frontend'])->name('shell.frontend');
        Route::get('/shell/admin', [ShellPreviewController::class, 'admin'])
            ->middleware(['auth', 'active', 'verified', 'can:access-admin', 'two-factor.confirmed'])
            ->name('shell.admin');
        Route::get('/concepts', [DesignLabController::class, 'index'])->name('concepts.index');
        Route::get('/concepts/{concept}', [DesignLabController::class, 'show'])
            ->where('concept', '[0-9]{2}-[a-z0-9-]+')
            ->name('concepts.show');
        Route::get('/{section?}', UiPreviewController::class)
            ->where('section', 'foundations|components|patterns|states|admin')
            ->name('index');
    });
}
