@component('mail::message')
# 儿童信息
@component('mail::table')
| 属性|值 |
| :-------------:|:--------:|
|姓名|{{ $appointment->name }} |
|性别|{{ \App\Models\Appointment::$genderMap[$appointment->gender] }}|
|年龄|{{ $appointment->age }}岁|
|联系方式|{{ $appointment->phone }}|
|家庭住址|{{ $appointment->address }}|
|预约时间|{{ $appointment->created_at }}|

@endcomponent

# 试课信息
@component('mail::table')
| 试课时间       |
| :-------- |
|{{ implode('，', $appointment->meet_date) }} |
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'green'])
	查看更多预约信息
@endcomponent
@endcomponent


