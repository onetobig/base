<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AppointmentRequest;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppointmentsController extends Controller
{
    public function store(AppointmentRequest $request)
    {
        $appointment = Appointment::query()->create($request->only([
            'name',
            'age',
            'phone',
            'meet_date',
            'degree',
            'gender',
        ]));


        return $this->response->array($appointment->toArray())->setStatusCode(201);
    }
}
