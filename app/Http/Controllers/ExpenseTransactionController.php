<?php

namespace App\Http\Controllers;

use App\Services\ExpenseTransactionService;
use Illuminate\Http\Request;

class ExpenseTransactionController extends Controller
{
    protected $service;

    public function __construct(ExpenseTransactionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|integer',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255'
        ]);

        return response()->json($this->service->store($validated));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'account_id' => 'required|integer',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255'
        ]);

        return response()->json($this->service->update($id, $validated));
    }

    public function destroy($id)
    {
        return response()->json($this->service->destroy($id));
    }
}
