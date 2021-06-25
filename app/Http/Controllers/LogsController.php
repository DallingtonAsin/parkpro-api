<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\activityLog;
use App\DataTables\LogsDataTable;

class LogsController extends Controller
{
    public function index()
    {
        try {
            $totl_logs = activityLog::count();
            return view('pages.logs.activity_logs')
                      ->with(compact('totl_logs'));
        } catch (\Exception $ex) {
            dd($ex->getMessage());
        }
    }

    public function getActivityLogs(LogsDataTable $dataTable)
    {
        try {
            return $dataTable->render('pages.logs.activity_logs');
        } catch (\Exception $ex) {
            dd($ex->getMessage());
        }
    }
}
