<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Extensions\ActivateTheme;
use App\Actions\Admin\Extensions\CleanupExtensionReleases;
use App\Actions\Admin\Extensions\InspectExtensionPackage;
use App\Actions\Admin\Extensions\InstallExtensionPackage;
use App\Actions\Admin\Extensions\RollbackExtension;
use App\Actions\Admin\Extensions\TogglePlugin;
use App\Actions\Admin\Extensions\UpgradeExtensionPackage;
use App\Application\Admin\Queries\ExtensionReadModel;
use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Extensions\CleanupExtensionRequest;
use App\Http\Requests\Admin\Extensions\InstallExtensionRequest;
use App\Http\Requests\Admin\Extensions\RollbackExtensionRequest;
use App\Http\Requests\Admin\Extensions\UpgradeExtensionRequest;
use App\Http\Requests\Admin\Extensions\UploadExtensionRequest;
use App\Models\Extension;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

final class ExtensionController extends Controller
{
    public function index(ExtensionReadModel $query): View
    {
        return view('admin.extensions.index', [
            'extensions' => $query->index(),
        ]);
    }

    public function show(Extension $extension, ExtensionReadModel $query): View
    {
        return view('admin.extensions.show', [
            'extension' => $query->show($extension),
        ]);
    }

    public function upload(UploadExtensionRequest $request, InspectExtensionPackage $action): View
    {
        $package = $request->file('package');
        abort_unless($package !== null, 422);

        return view('admin.extensions.inspect', $action->handle($package, (string) $request->validated('type')));
    }

    public function install(InstallExtensionRequest $request, InstallExtensionPackage $action, PrivilegedAuditLogger $audit): RedirectResponse
    {
        try {
            $stored = (string) $request->validated('stored');
            $type = (string) $request->validated('type');
            $enable = (bool) $request->validated('enable', false);
            $action->handle($stored, $type, $enable);

            /** @var User $actor */
            $actor = $request->user();
            $audit->record(
                event: 'extension.install',
                description: 'Extension installed.',
                subject: null,
                actor: $actor,
                after: ['type' => $type, 'enabled' => $enable],
                context: ['stored_package' => basename($stored)],
            );

            return redirect()->route('admin.extensions.index')->with('status', 'Extension installed successfully.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['package' => 'The extension could not be installed. Review the logs for details.']);
        }
    }

    public function upgrade(UpgradeExtensionRequest $request, Extension $extension, UpgradeExtensionPackage $action, PrivilegedAuditLogger $audit): RedirectResponse
    {
        $package = $request->file('package');
        abort_unless($package !== null, 422);

        try {
            $before = ['version' => $extension->active_version, 'enabled' => (bool) $extension->enabled];
            $action->handle($extension, $package);
            $extension->refresh();

            /** @var User $actor */
            $actor = $request->user();
            $audit->record(
                event: 'extension.upgrade',
                description: 'Extension upgraded.',
                subject: $extension,
                actor: $actor,
                before: $before,
                after: ['version' => $extension->active_version, 'enabled' => (bool) $extension->enabled],
            );

            return back()->with('status', 'Extension upgraded successfully.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['package' => 'The extension could not be upgraded. Review the logs for details.']);
        }
    }

    public function toggle(Extension $extension, TogglePlugin $action, PrivilegedAuditLogger $audit): RedirectResponse
    {
        $wasEnabled = $action->handle($extension);
        $extension->refresh();

        /** @var User $actor */
        $actor = auth()->user();
        $audit->record(
            event: $wasEnabled ? 'extension.disable' : 'extension.enable',
            description: $wasEnabled ? 'Plugin disabled.' : 'Plugin enabled.',
            subject: $extension,
            actor: $actor,
            before: ['enabled' => $wasEnabled],
            after: ['enabled' => (bool) $extension->enabled],
        );

        return back()->with('status', $wasEnabled ? 'Plugin disabled.' : 'Plugin enabled.');
    }

    public function activate(Extension $extension, ActivateTheme $action, PrivilegedAuditLogger $audit): RedirectResponse
    {
        $action->handle($extension);

        /** @var User $actor */
        $actor = auth()->user();
        $audit->record(
            event: 'extension.activate-theme',
            description: 'Theme activated.',
            subject: $extension,
            actor: $actor,
            after: ['active_theme' => $extension->slug],
        );

        return back()->with('status', 'Theme activated.');
    }

    public function rollback(RollbackExtensionRequest $request, Extension $extension, RollbackExtension $action, PrivilegedAuditLogger $audit): RedirectResponse
    {
        $version = $request->validated('version');
        $before = ['version' => $extension->active_version];
        $action->handle($extension, is_string($version) ? $version : null);
        $extension->refresh();

        /** @var User $actor */
        $actor = $request->user();
        $audit->record(
            event: 'extension.rollback',
            description: 'Extension rollback completed.',
            subject: $extension,
            actor: $actor,
            before: $before,
            after: ['version' => $extension->active_version],
            context: ['requested_version' => is_string($version) ? $version : null],
        );

        return back()->with('status', 'Rollback completed.');
    }

    public function cleanup(CleanupExtensionRequest $request, Extension $extension, CleanupExtensionReleases $action, PrivilegedAuditLogger $audit): RedirectResponse
    {
        $keep = (int) $request->validated('keep', 2);
        $deleted = $action->handle($extension, $keep);

        /** @var User $actor */
        $actor = $request->user();
        $audit->record(
            event: 'extension.cleanup',
            description: 'Old extension releases cleaned up.',
            subject: $extension,
            actor: $actor,
            context: ['keep' => $keep, 'deleted' => $deleted],
        );

        return back()->with('status', "Removed {$deleted} old release(s).");
    }
}
