<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\AttendanceBreak;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $staff = User::where('role', 'user')
            ->orderBy('id')
            ->get();

        return view(
            'admin.users.index',
            compact('staff')
        );
    } //
    public function attendance($id)
    {
        $staff = User::findOrFail($id);

        // 表示する月（例: 2026-03）
        $month = request('month', \Carbon\Carbon::now()->format('Y-m'));

        $start = \Carbon\Carbon::parse($month)->startOfMonth();
        $end   = \Carbon\Carbon::parse($month)->endOfMonth();

        // 対象月の勤怠（work_dateで絞る）
        $attendanceRows = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('work_date')
            ->get();

        // work_date => Attendance のMap
        $attendances = $attendanceRows->keyBy(function ($a) {
            return \Carbon\Carbon::parse($a->work_date)->format('Y-m-d');
        });

        // attendance_id 一覧
        $attendanceIds = $attendanceRows->pluck('id')->all();

        // 休憩合計（秒）を attendance_id ごとに集計
        // ※break_endがnull（休憩中）のものは集計しない
        $breakSecondsByAttendanceId = \App\Models\AttendanceBreak::selectRaw(
            'attendance_id,
             SUM(TIMESTAMPDIFF(SECOND, break_start, break_end)) AS sec'
        )
            ->whereIn('attendance_id', $attendanceIds)
            ->whereNotNull('break_end')
            ->groupBy('attendance_id')
            ->pluck('sec', 'attendance_id'); // [attendance_id => seconds]

        // 月の全日付を生成（1日〜末日）
        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        return view('admin.users.attendance', compact(
            'staff',
            'month',
            'days',
            'attendances',
            'breakSecondsByAttendanceId'
        ));
    }
        
    public function csv($id)
    {
        $staff = User::findOrFail($id);

        $month = request('month', \Carbon\Carbon::now()->format('Y-m'));

        $start = \Carbon\Carbon::parse($month)->startOfMonth();
        $end   = \Carbon\Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($attendances, $staff, $month) {

            $handle = fopen('php://output', 'w');

            // Excel文字化け対策（BOM）
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // タイトル
            fputcsv($handle, [$staff->name . ' ' . $month . ' 勤怠一覧']);

            // 空行
            fputcsv($handle, []);

            // ヘッダー
            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計'
            ]);

            foreach ($attendances as $attendance) {

                $clockIn = $attendance->clock_in
                    ? \Carbon\Carbon::parse($attendance->clock_in)
                    : null;

                $clockOut = $attendance->clock_out
                    ? \Carbon\Carbon::parse($attendance->clock_out)
                    : null;

                $breakSeconds = 0;

                $breaks = AttendanceBreak::where(
                    'attendance_id',
                    $attendance->id
                )->get();

                foreach ($breaks as $break) {

                    if ($break->break_start && $break->break_end) {

                        $start = \Carbon\Carbon::parse($break->break_start);
                        $end   = \Carbon\Carbon::parse($break->break_end);

                        $breakSeconds += $end->diffInSeconds($start);
                    }
                }

                $breakTime = gmdate('H:i', $breakSeconds);

                $workSeconds = 0;

                if ($clockIn && $clockOut) {

                    $workSeconds =
                        $clockOut->diffInSeconds($clockIn)
                        - $breakSeconds;
                }

                $workTime = gmdate('H:i', $workSeconds);

                fputcsv($handle, [

                    \Carbon\Carbon::parse($attendance->work_date)
                        ->format('Y/m/d'),

                    $clockIn
                        ? $clockIn->format('H:i')
                        : '',

                    $clockOut
                        ? $clockOut->format('H:i')
                        : '',

                    $breakTime,

                    $workTime

                ]);
            }

            fclose($handle);
        });

        $filename = 'attendance_' . $month . '.csv';

        $response->headers->set(
            'Content-Type',
            'text/csv; charset=UTF-8'
        );

        $response->headers->set(
            'Content-Disposition',
            "attachment; filename=$filename"
        );

        return $response;
    }}
