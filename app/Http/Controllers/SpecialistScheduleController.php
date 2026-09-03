<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpecialistScheduleRequest;
use App\Models\Branch;
use App\Models\SpecialistSchedule;
use App\Models\User;

class SpecialistScheduleController extends Controller
{
    public function index()
    {
        $schedules = SpecialistSchedule::query()
            ->with(['branch:id,name', 'user:id,name,role'])
            ->orderBy('branch_id')
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->get();

        return view('horarios.index', compact('schedules'));
    }

    public function create()
    {
        $schedule = new SpecialistSchedule([
            'service_type' => 'nutrition',
            'active' => true,
        ]);

        return view('horarios.create', [
            'schedule' => $schedule,
            'branches' => $this->branches(),
            'nutritionUser' => $this->nutritionUser(),
            'weekdays' => SpecialistSchedule::WEEKDAYS,
            'serviceTypes' => SpecialistSchedule::SERVICE_TYPES,
        ]);
    }

    public function store(SpecialistScheduleRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $this->nutritionUser()->id;
        $data['service_type'] = 'nutrition';
        $data['active'] = $request->boolean('active');

        SpecialistSchedule::create($data);

        return redirect()
            ->route('horarios.index')
            ->with('success', 'Horario de nutrición creado correctamente.');
    }

    public function edit(SpecialistSchedule $horario)
    {
        return view('horarios.edit', [
            'schedule' => $horario,
            'branches' => $this->branches(),
            'nutritionUser' => $this->nutritionUser(),
            'weekdays' => SpecialistSchedule::WEEKDAYS,
            'serviceTypes' => SpecialistSchedule::SERVICE_TYPES,
        ]);
    }

    public function update(SpecialistScheduleRequest $request, SpecialistSchedule $horario)
    {
        $data = $request->validated();
        $data['user_id'] = $this->nutritionUser()->id;
        $data['service_type'] = 'nutrition';
        $data['active'] = $request->boolean('active');

        $horario->update($data);

        return redirect()
            ->route('horarios.index')
            ->with('success', 'Horario de nutrición actualizado correctamente.');
    }

    public function destroy(SpecialistSchedule $horario)
    {
        $horario->delete();

        return redirect()
            ->route('horarios.index')
            ->with('success', 'Horario eliminado correctamente.');
    }

    private function branches()
    {
        return Branch::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function nutritionUser(): User
    {
        $userId = (int) config('services.vitalfit_bot.nutrition_user_id');

        abort_if($userId <= 0, 500, 'No está configurado VITALFIT_NUTRITION_USER_ID.');

        return User::query()
            ->where('active', true)
            ->findOrFail($userId);
    }
}