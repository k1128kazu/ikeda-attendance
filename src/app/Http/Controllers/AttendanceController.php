<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
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

        if (!$attendance) {
            $status = 'off';
        } else {
            $status = $attendance->status;
        }

        return view('attendance.index', compact('attendance', 'status'));
    }


    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $exists = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->exists();

        if ($exists) {
            return redirect('/attendance');
        }

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => Carbon::now()->format('H:i:s'),
            'status' => 'working'
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
            'break_start' => Carbon::now()
        ]);

        $attendance->update([
            'status' => 'breaking'
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
                'break_end' => Carbon::now()
            ]);
        }

        $attendance->update([
            'status' => 'working'
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
            'clock_out' => Carbon::now()->format('H:i:s'),
            'status' => 'finished'
        ]);

        return redirect('/attendance');
    }

    public function list()
    {
        $user = auth()->user();

        $month = request('month', now()->format('Y-m'));

        $start = \Carbon\Carbon::parse($month)->startOfMonth();
        $end   = \Carbon\Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->with('breaks')
            ->orderBy('work_date')
            ->get();

        return view('attendance.list', compact(
            'attendances',
            'month'
        ));
    }
    public function show($id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        return view('attendance.show', compact('attendance'));
    }
}
