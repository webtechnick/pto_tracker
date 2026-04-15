<?php

namespace App\Http\Controllers\Api;

use App\Employee;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EmployeeRequest;

class EmployeeController extends Controller
{
    public function index(EmployeeRequest $request)
    {
        $perPage = $request->input('per_page', 100);

        $paginator = Employee::select(['id', 'name', 'is_contractor'])
            ->orderBy('name')
            ->paginate($perPage);

        $data = $paginator->getCollection()->map(function ($employee) {
            return [
                'id'            => $employee->id,
                'name'          => $employee->name,
                'active'        => true,
                'is_contractor' => $employee->is_contractor,
            ];
        });

        return response()->json([
            'data' => $data->all(),
            'meta' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
            ],
        ]);
    }
}
