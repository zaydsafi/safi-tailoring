<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailDelivery;

class EmailLogController extends Controller
{
    public function index()
    {
        $deliveries = EmailDelivery::latest()->paginate(25);

        // Which mail has been failing, so the summary points at the problem.
        $failedCount = EmailDelivery::where('status', EmailDelivery::STATUS_FAILED)->count();

        return view('admin.email-log.index', compact('deliveries', 'failedCount'));
    }
}
