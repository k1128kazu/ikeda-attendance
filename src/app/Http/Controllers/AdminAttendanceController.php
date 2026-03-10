<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\AdminAttendanceUpdateRequest;

class AdminAttendanceController extends Controller
{
    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        $clockIn  = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
        $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;
        $correction = \App\Models\AttendanceCorrectionRequest::where('attendance_id', $id)->latest()->first();
        
        return view('admin.attendances.detail', compact(
            'attendance',
            'clockIn',
            'clockOut',
            'correction'
        ));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        $attendance->clock_in  = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->note      = $request->note;
        $attendance->save();

        // 休憩1
        $break1 = $attendance->breaks[0] ?? new AttendanceBreak();
        $break1->attendance_id = $attendance->id;
        $break1->break_start = $request->break1_start;
        $break1->break_end   = $request->break1_end;
        $break1->save();

        // 休憩2
        if ($request->break2_start || $request->break2_end) {

            $break2 = $attendance->breaks[1] ?? new AttendanceBreak();
            $break2->attendance_id = $attendance->id;
            $break2->break_start = $request->break2_start;
            $break2->break_end   = $request->break2_end;
            $break2->save();
        }

        return redirect()
            ->route('admin.dashboard', ['date' => $attendance->work_date])
            ->with('success', '更新しました');
    }
}
