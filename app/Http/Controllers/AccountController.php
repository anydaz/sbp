<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountService;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Get all accounts with their current balances
     */
    public function index()
    {
        $accounts = $this->accountService->getAllAccounts();
        return response()->json($accounts);
    }

    /**
     * Get detailed account information including all entries
     */
    public function show($id)
    {
        $account = $this->accountService->getAccountDetails($id);
        return response()->json($account);
    }

    /**
     * Get accounts by type
     */
    public function getByType($type)
    {
        $accounts = $this->accountService->getAccountsByType($type);
        return response()->json($accounts);
    }
}
