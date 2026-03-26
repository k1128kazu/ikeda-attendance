<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $status = $attendance->status ?? 'off';

        return view('attendance.index', compact('attendance', 'status'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance) {
            return redirect('/attendance');
        }

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $today,
            'clock_in'  => Carbon::now(),
            'clock_out' => null,
            'status'    => 'working',
        ]);

        return redirect('/attendance');
    }

    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$attendance) {
            return redirect('/attendance');
        }

        $attendance->update([
            'clock_out' => Carbon::now(),
            'status'    => 'finished',
        ]);

        return redirect('/attendance');
    }

    public function breakIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$attendance) {
            return redirect('/attendance');
        }

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start'   => Carbon::now(),
        ]);

        $attendance->update([
            'status' => 'breaking',
        ]);

        return redirect('/attendance');
    }

    public function breakOut()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$attendance) {
            return redirect('/attendance');
        }

        $break = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        if ($break) {
            $break->update([
                'break_end' => Carbon::now(),
            ]);
        }

        $attendance->update([
            'status' => 'working',
        ]);

        return redirect('/attendance');
    }

    public function list(Request $request)
    {
        $user = Auth::user();

        $currentMonth = $request->get('month')
            ? Carbon::parse($request->get('month'))->startOfMonth()
            : Carbon::now()->startOfMonth();
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->orderBy('work_date', 'asc')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        $period = \Carbon\CarbonPeriod::create(
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        );

        $list = [];

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');

            $list[] = [
                'date' => $date,
                'attendance' => $attendances->get($key)
            ];
        }

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        $month = $currentMonth->format('Y-m');

        return view('attendance.list', compact(
            'list',
            'month',
            'prevMonth',
            'nextMonth'
        ));
    }
    public function show($id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        $isPending = \App\Models\AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        return view('attendance.show', compact('attendance', 'isPending'));
    }
}
