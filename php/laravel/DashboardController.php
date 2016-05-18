<?php

namespace App\Http\Controllers;

use App\Http\Requests\DateIntervalRequest;
use App\Http\Requests\ReportRequest;
use App\Model\Customer;
use App\Services\RecipientService;
use App\Services\ReportService;
use App\Utils\ReportDateIntervals;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller {

    /** @var RecipientService */
    private $recipientService;
    /** @var ReportService */
    private $reportService;

    public function __construct(RecipientService $recipientService, ReportService $reportService)
    {
        $this->recipientService = $recipientService;
        $this->reportService = $reportService;
    }

    public function getOverview()
    {
        $data["customers"] = Customer::all(["id", "name"]);
        return view('adminportal.dashboard', $data);
    }

    public function generateReport(ReportRequest $reportRequest)
    {
        $startTime = Carbon::createFromFormat("m/d/Y", $reportRequest["startDate"])->startOfDay();
        $endTime = Carbon::createFromFormat("m/d/Y", $reportRequest["endDate"])->endOfDay();
        $customerIds = $reportRequest["customerIds"];

        return new JsonResponse($this->reportService->generateReport($startTime, $endTime, $customerIds));
    }

    public function getTableData()
    {
        return new JsonResponse(["data" => $this->recipientService->getDashboardRecipientTableData()]);
    }

    public function getDatesForInterval(DateIntervalRequest $request)
    {
        return ReportDateIntervals::getDateRange($request->get("interval"));
    }

}
