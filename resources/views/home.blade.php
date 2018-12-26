@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script>
    $.ajax({
        method: "POST",
        url: "http://edu.demo/api/appointments",
        data: {
            name: "阮雨",
            age: 5,
            phone: 13055246008,
            meet_date: "本周六下午三点",
            hobbies: ["唱歌", "跳舞"],
            gender: 0,
        }
    });
</script>
@endsection
