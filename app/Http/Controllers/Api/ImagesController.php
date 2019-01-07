<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AppointmentRequest;
use App\Http\Transformers\ImageTransformer;
use App\Jobs\SendAppointMailToAdmin;
use App\Models\Appointment;
use App\Models\Image;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ImagesController extends Controller
{
    public function index()
    {
        $images = Image::query()->get();
        return $this->response->collection($images, new ImageTransformer());
    }
}
