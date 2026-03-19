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

        $isPending = \App\Models\AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        return view('admin.attendances.detail', compact(
            'attendance',
            'clockIn',
            'clockOut',
            'isPending'
        ));
    }
    
    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // 勤怠更新
        $attendance->clock_in  = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->note      = $request->note;
        $attendance->save();

        // 既存休憩削除
        AttendanceBreak::where('attendance_id', $attendance->id)->delete();

        /*
        |--------------------------------------------------------------------------
        | 休憩（可変対応）
        |--------------------------------------------------------------------------
        */
        $breakStarts = $request->break_start ?? [];
        $breakEnds   = $request->break_end ?? [];

        foreach ($breakStarts as $index => $start) {

            $end = $breakEnds[$index] ?? null;

            if ($start && $end) {
                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start'   => $start,
                    'break_end'     => $end,
                ]);
            }
        }

        // リダイレクト（既存維持）
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
