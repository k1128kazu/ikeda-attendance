<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('work_date', $date)
            ->orderBy('user_id')
            ->get();

        $prevDate = (clone $date)->subDay()->toDateString();
        $nextDate = (clone $date)->addDay()->toDateString();

        return view('admin.attendances.index', compact('attendances', 'date', 'prevDate', 'nextDate'));
    }
}
