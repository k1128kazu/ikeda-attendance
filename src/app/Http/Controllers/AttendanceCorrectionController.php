<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest as CorrectionRequest;
use App\Models\AttendanceCorrectionBreak;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AttendanceCorrectionRequest;

class AttendanceCorrectionController extends Controller
{
    public function create($id)
    {
        return redirect()->route('attendance.show', $id);
    }

    public function store(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $user = Auth::user();

        DB::transaction(function () use ($request, $attendance, $user) {

            $correction = CorrectionRequest::create([
                'attendance_id'      => $attendance->id,
                'user_id'            => $user->id,
                'request_clock_in'   => $request->filled('clock_in') ? $request->clock_in : null,
                'request_clock_out'  => $request->filled('clock_out') ? $request->clock_out : null,
                'request_note'       => $request->note,
                'status'             => 'pending'
            ]);

            foreach ($request->break_start ?? [] as $index => $breakStart) {

                $breakEnd = $request->break_end[$index] ?? null;

                if (empty($breakStart) && empty($breakEnd)) {
                    continue;
                }

                AttendanceCorrectionBreak::create([
                    'attendance_correction_request_id' => $correction->id,
                    'break_start' => $breakStart ?: null,
                    'break_end'   => $breakEnd ?: null,
                ]);
            }
        });

        return redirect()
            ->route('corrections.index')
            ->with('success', '修正申請を送信しました。');
    }

    public function index()
    {
        $status = request('status', 'pending');

        $requests = \App\Models\AttendanceCorrectionRequest::with(['attendance', 'user'])
            ->where('user_id', auth()->id())
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('corrections.index', compact('requests', 'status'));
    }

    public function show($id)
    {
        $request = \App\Models\AttendanceCorrectionRequest::with([
            'attendance',
            'breaks',
            'attendance.user'
        ])->findOrFail($id);

        return view('corrections.show', compact('request'));
    }
}
