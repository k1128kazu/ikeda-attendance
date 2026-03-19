@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="attendance-wrapper">

    <div class="attendance-status">
        @if($status === 'working')
        出勤中
        @elseif($status === 'breaking')
        休憩中
        @elseif($status === 'finished')
        退勤済
        @else
        勤務外
        @endif
    </div>
    <div class="attendance-date">
        {{ now()->isoFormat('YYYY年M月D日(ddd)') }}
    </div>

    <div class="attendance-time">
        {{ now()->format('H:i') }}
    </div>


    {{-- 出勤前 --}}
    @if($status === 'off')

    <form method="POST" action="/attendance/clock-in" novalidate>
        @csrf
        <button class="btn-black">
            出勤
        </button>
    </form>

    @endif


    {{-- 出勤中 --}}
    @if($status === 'working')

    <div class="attendance-btn-group">

        <form method="POST" action="/attendance/clock-out" novalidate>
            @csrf
            <button class="btn-black">
                退勤
            </button>
        </form>

        <form method="POST" action="/attendance/break-in" novalidate>
            @csrf
            <button class="btn-white">
                休憩入
            </button>
        </form>

    </div>

    @endif


    {{-- 休憩中 --}}
    @if($status === 'breaking')

    <div class="attendance-btn-group">

        <form method="POST" action="/attendance/clock-out" novalidate>
            @csrf
            <button class="btn-black">
                退勤
            </button>
        </form>

        <form method="POST" action="/attendance/break-out" novalidate>
            @csrf
            <button class="btn-white">
                休憩戻
            </button>
        </form>

    </div>

    @endif


    {{-- 退勤後 --}}
    @if($status === 'finished')

    <div class="attendance-finish">
        お疲れ様でした。
    </div>

    @endif

</div>

@endsection