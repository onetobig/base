<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\TeacherTransformer;
use App\Models\Teachers;
use Illuminate\Http\Request;

class TeachersController extends Controller
{
    public function index()
    {
        $teachers = Teachers::query()->get();

        return $this->response->collection($teachers, new TeacherTransformer());
    }
}
