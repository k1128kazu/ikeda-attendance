<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\AdminAttendanceUpdateRequest;

class AdminAttendanceController extends Controller
{
    public function detail(Request $request, $id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        $clockIn  = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
        $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

        return view('admin.attendances.detail', compact(
            'attendance',
            'clockIn',
            'clockOut'
        ));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        $attendance->clock_in  = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->note      = $request->note;
        $attendance->save();

        AttendanceBreak::where('attendance_id', $attendance->id)->delete();

        if ($request->break1_start && $request->break1_end) {

            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => $request->break1_start,
                'break_end' => $request->break1_end
            ]);
        }

        if ($request->break2_start && $request->break2_end) {

            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => $request->break2_start,
                'break_end' => $request->break2_end
            ]);
        }

        if ($request->from === 'staff') {
            return redirect()
                ->route('admin.staff.attendance', $attendance->user_id)
                ->with('success', '更新しました');
        }

        return redirect()
            ->route('admin.dashboard', ['date' => $attendance->work_date])
            ->with('success', '更新しました');
    }
}
