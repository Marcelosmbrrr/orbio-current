<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Requests\Manager\CreateManagerRequest;
use App\Http\Requests\Manager\EditManagerRequest;
use App\Notifications\UserCreationNotification;
use App\Http\Resources\Users\UserResource;

class ManagerController extends Controller
{
    function __construct(User $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        Gate::authorize('managers:read');

        $order_by = request("order_by", "id");
        $limit = request("limit", "10");
        $page = request("page", "1");
        $search = request("search", "");
        $group = request("group", "all");

        $data = $this->model
            ->withTrashed()
            ->where("role", "gerente")
            ->filter($group) // scope
            ->search($search) // scope
            ->orderBy($order_by)
            ->paginate((int) $limit, ['*'], 'page', (int) $page);

        return Inertia::render("Authenticated/Managers/Index", [
            "data" => new UserResource($data),
            "queryParams" => request()->query() ?: null,
            "success" => session('success'),
        ]);
    }

    public function create()
    {
        Gate::authorize('managers:write');

        return Inertia::render("Authenticated/Managers/CreateManager");
    }

    public function store(CreateManagerRequest $request)
    {
        Gate::authorize('managers:write');

        $manager = $this->model->create([
            ...$request->validated(),
            "role" => "gerente",
            'public_id' => Str::uuid(),
        ]);

        $manager->address()->create();
        $manager->document()->create();
        $manager->contact()->create();

        event(new Registered($manager));
        //$manager->notify(new UserCreationNotification($request->password));

        return redirect()->route('managers.index', ['search' => $manager->public_id->toString()])
            ->with('success', 'Gerente criado!');
    }

    public function show(string $id)
    {
        Gate::authorize('managers:read');

        $manager = $this->model->withTrashed()->where("public_id", $id)->first();

        return Inertia::render("Authenticated/Managers/ShowManager", [
            "manager" => [
                "id" => $manager->public_id,
                "name" => $manager->name,
                "role" => $manager->role,
                "email" => $manager->email,
                "status" => $manager->trashed() ? "Deletado" : ($manager->status ? "Ativo" : "Inativo"),
                "created_at" => $manager->created_at->format('d/m/Y'),
                "updated_at" => $manager->updated_at->format('d/m/Y'),
                "deleted_at" => $manager->deleted_at
            ]
        ]);
    }

    public function edit(string $id)
    {
        Gate::authorize('managers:write');

        $manager = $this->model->withTrashed()->where("public_id", $id)->first();

        return Inertia::render("Authenticated/Managers/EditManager", [
            "tenant" => [
                "id" => $manager->public_id,
                "name" => $manager->name,
                "email" => $manager->email
            ]
        ]);
    }

    public function update(EditManagerRequest $request, string $id)
    {
        Gate::authorize('managers:write');

        $user = $this->model->withTrashed()->where("public_id", $id)->first();
        $user->update($request->validated());

        return redirect()->route('managers.index', ['search' => $user->public_id])
            ->with('success', "Gerente editado!");
    }

    public function destroy()
    {
        Gate::authorize('managers:write');

        $ids = explode(",", request("ids"));

        DB::transaction(function () use ($ids) {
            $this->model->whereIn("public_id", $ids)->delete();
        });

        return redirect()->route('managers.index', ['group' => "deleted"])
            ->with('success', "Gerente(s) Deletado(s)!");
    }
}
