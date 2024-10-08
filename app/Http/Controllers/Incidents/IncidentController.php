<?php

namespace App\Http\Controllers\Incidents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Incidents\CreateIncidentRequest;
use App\Http\Requests\Incidents\EditIncidentRequest;
use App\Models\Incident;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class IncidentController extends Controller
{
    public function __construct(Incident $incidentModel, ServiceOrder $serviceOrderModel)
    {
        $this->incidentModel = $incidentModel;
        $this->serviceOrderModel = $serviceOrderModel;
    }

    public function create(string $service_order_id)
    {
        Gate::authorize('service-orders:edit');

        return Inertia::render('Authenticated/Incidents/CreateIncident', [
            'service_order_id' => $service_order_id,
        ]);
    }

    public function store(CreateIncidentRequest $request, string $service_order_id)
    {
        Gate::authorize('service-orders:edit');

        $service_order = $this->serviceOrderModel->where('public_id', $service_order_id)->first();

        $this->incidentModel->create([
            ...$request->validated(),
            'public_id' => Str::uuid(),
            'service_order_id' => $service_order->id,
        ]);

        return redirect()->route('service-orders.show', ['service_order' => $service_order_id])
            ->with('success', 'A criação do incidente foi bem sucedida');
    }

    public function edit(string $service_order_id, string $incident_id)
    {
        Gate::authorize('service-orders:edit');

        $incident = $this->incidentModel->where('public_id', $incident_id)->first();

        return Inertia::render('Authenticated/Incidents/EditIncident', [
            'incident_id' => $incident_id,
            'service_order_id' => $service_order_id,
            'incident' => [
                'id' => $incident->public_id,
                'type' => $incident->type,
                'date' => $incident->date,
                'description' => $incident->description,
            ],
        ]);
    }

    public function update(EditIncidentRequest $request, string $service_order_id, string $incident_id)
    {
        Gate::authorize('service-orders:edit');

        $incident = $this->incidentModel->where('public_id', $incident_id)->first();

        $incident->update($request->validated());

        return redirect()->route('service-orders.show', ['service_order' => $service_order_id])
            ->with('success', 'A edição do incidente foi bem sucedida');
    }

    public function destroy(string $service_order_id)
    {
        Gate::authorize('service-orders:edit');

        $ids = explode(',', request('ids'));

        DB::transaction(function () use ($ids) {
            $log = $this->incidentModel->whereIn('public_id', $ids)->first();
            $log->delete();
        });

        return redirect()->route('service-orders.show', ['service_order' => $service_order_id])
            ->with('success', 'Os incidentes selecionados foram permanentemente deletados');
    }
}
