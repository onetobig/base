<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/18
 * Time: 14:13
 */

namespace App\Admin\Extensions;

use App\Models\Appointment;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Facades\Excel;

class AppointmentExcelExpoter extends AbstractExporter
{
    public function export()
    {
        $date = now();
        Excel::create($date->toDateString() . '预约列表', function ($excel) {
            $excel->sheet('Sheetname', function($sheet) {
                // 这段逻辑是从表格数据中取出需要导出的字段
                $rows = collect($this->getData())->map(function ($item) {
                    $item = array_only($item, ['id', 'name', 'phone', 'created_at', 'meet_date', 'gender', 'hobbies', 'courses']);
                    $item['gender'] = Appointment::$genderMap[$item['gender']] ?? '未知';
                    $item['hobbies'] = implode("，", $item['hobbies']);
                    $item['courses'] = implode("，", $item['courses']);
                    return $item;
                });
                $rows = $rows->toArray();
                array_unshift($rows, ['id', '姓名', '电话', '提交时间', '体验时间', '学员性别', '兴趣及爱好', '试课程班别']);
                $sheet->rows($rows);
            });
        })->export('xls');
    }
}