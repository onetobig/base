<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AppointmentRequest;
use App\Jobs\SendAppointMailToAdmin;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppointmentsController extends Controller
{
    public function store(AppointmentRequest $request)
    {
        $data = $request->only([
            'name',
            'age',
            'phone',
            'meet_date',
            'gender',
            'address',
            'courses',
        ]);
        $appointment = Appointment::query()->create($data);

        $this->dispatch(new SendAppointMailToAdmin($appointment));
        return $this->response->array($appointment->toArray())->setStatusCode(201);
    }
}
