<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/15
 * Time: 09:36
 */

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Api\Helpers\PaginateHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use ApiResponse;
    use PaginateHelper;


    /**
     * 检查参数包含 id
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateIdField(?Request $request = null, $others_rules = [], $messages = [], $attributes = [])
    {
        if (!$request) {
            $request = request();
        }
        $this->validate($request, array_merge([
            'id' => 'required|integer|min:1',
        ], $others_rules), $messages, $attributes);
        return $request->input('id');
    }
}
