<?php

namespace App\Http\Controllers;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'clinic.context']);
    }

    public function index()
    {
        return view('expense.index');
    }

}
