<?php

namespace App\Http\Controllers\Admin;

use App\Features\Admin\Actions\UpsertAdminResourceAction;
use App\Features\Admin\Queries\AdminDashboardQuery;
use App\Features\Admin\Support\AdminResourceRegistry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertAdminResourceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminResourceController extends Controller
{
    public function __construct(
        private readonly AdminResourceRegistry $registry,
        private readonly AdminDashboardQuery $query,
    ) {}

    public function create(Request $request, string $resource): View
    {
        $definition = $this->registry->definition($resource);
        abort_unless($request->user()?->can($definition['permission']), 403);

        return view('admin.resources.form', [
            'definition' => $definition,
            'resource' => $resource,
            'model' => null,
            'registry' => $this->registry,
            'mode' => 'create',
            'portal' => $this->query->forUser($request->user()),
            'navigation' => $this->query->navigation(),
        ]);
    }

    public function store(UpsertAdminResourceRequest $request, string $resource, UpsertAdminResourceAction $action): RedirectResponse
    {
        $definition = $this->registry->definition($resource);
        $model = $action->handle($resource, $request->validated(), actor: $request->user());

        return redirect()
            ->route($definition['index_route'])
            ->with('status', $definition['singular'].' berhasil ditambahkan.');
    }

    public function edit(Request $request, string $resource, int $id): View
    {
        $definition = $this->registry->definition($resource);
        abort_unless($request->user()?->can($definition['permission']), 403);

        return view('admin.resources.form', [
            'definition' => $definition,
            'resource' => $resource,
            'model' => $this->registry->model($resource, $id),
            'registry' => $this->registry,
            'mode' => 'edit',
            'portal' => $this->query->forUser($request->user()),
            'navigation' => $this->query->navigation(),
        ]);
    }

    public function update(UpsertAdminResourceRequest $request, string $resource, int $id, UpsertAdminResourceAction $action): RedirectResponse
    {
        $definition = $this->registry->definition($resource);
        $action->handle($resource, $request->validated(), $this->registry->model($resource, $id), $request->user());

        return redirect()
            ->route($definition['index_route'])
            ->with('status', $definition['singular'].' berhasil diperbarui.');
    }
}
