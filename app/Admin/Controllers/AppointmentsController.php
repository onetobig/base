<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\AppointmentExcelExpoter;
use App\Models\Appointment;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;

class AppointmentsController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('预约列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Appointment);
        $grid->exporter(new AppointmentExcelExpoter());
        $date = Request::get('created_at');
        if (!is_array($date)) {
            $date = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
            $grid->model()->where('created_at', '>=', $date);
        }
        $grid->filter(function ($filter) {
            $filter->like('name', '姓名');
            $filter->between('created_at', '提交时间')->datetime(['format' => 'YYYY-MM-DD']);
            $filter->equal('gender', '性别')->select(Appointment::$genderMap);
        });
        $grid->id('Id');
        $grid->name('姓名');
        $grid->age('年龄');
        $grid->phone('手机');
        $grid->address('家庭住址');
        $grid->gender('性别')->display(function ($value) {
            return Appointment::$genderMap[$value] ?? "未知";
        });
        $grid->meet_date('体验时间')->display(function ($value) {
            return implode("<br>", $value);
        });
        $grid->created_at('提交时间');
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });
//        $grid->created_at('Created at');
//        $grid->updated_at('Updated at');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Appointment::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->age('Age');
        $show->phone('Phone');
        $show->meet_date('Meet date');
        $show->gender('Gender');
        $show->degree('Degree');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Appointment);

        $form->text('name', 'Name');
        $form->number('age', 'Age');
        $form->mobile('phone', 'Phone');
        $form->datetime('meet_date', 'Meet date')->default(date('Y-m-d H:i:s'));
        $form->switch('gender', 'Gender');
        $form->text('degree', 'Degree');

        return $form;
    }
}
