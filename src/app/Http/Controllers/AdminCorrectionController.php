<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceCorrectionBreak;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class AdminCorrectionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $requests = AttendanceCorrectionRequest::with([
            'user',
            'attendance'
        ])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.corrections.index', compact('requests', 'status'));
    }

    public function show($id)
    {
        $request = AttendanceCorrectionRequest::with('attendance.user')->findOrFail($id);

        $attendance = $request->attendance;

        $breaks = AttendanceCorrectionBreak::where(
            'attendance_correction_request_id',
            $request->id
        )->get();

        return view('admin.corrections.approve', compact(
            'request',
            'attendance',
            'breaks'
        ));
    }
    
    public function approve($id)
    {
        $correction = AttendanceCorrectionRequest::findOrFail($id);

        $attendance = Attendance::findOrFail($correction->attendance_id);

        $attendance->update([
            'clock_in' => $correction->request_clock_in,
            'clock_out' => $correction->request_clock_out,
            'note' => $correction->request_note,
        ]);

        AttendanceBreak::where('attendance_id', $attendance->id)->delete();

        $breaks = AttendanceCorrectionBreak::where(
            'attendance_correction_request_id',
            $correction->id
        )->get();

        foreach ($breaks as $break) {
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => $break->break_start,
                'break_end' => $break->break_end,
            ]);
        }

        $correction->update([
            'status' => 'approved'
        ]);

        return redirect('/stamp_correction_request/list');
    }
}
