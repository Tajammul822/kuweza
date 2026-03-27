<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanPaymentRule;

class LoanPaymentRuleController extends Controller
{
    public function index()
    {
        $rules = LoanPaymentRule::orderBy('min_amount', 'asc')->get();
        return view('admin.rules.index', compact('rules'));
    }

    
    public function create()
    {
        return view('admin.rules.create');
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'duration_days' => 'required|integer|min:1',
            'installment_type' => 'required|in:SINGLE,WEEKLY,MONTHLY',
            'penalty_type' => 'required|in:FIXED,PERCENTAGE',
            'penalty_value' => 'required|numeric|min:0',
            'grace_period_days' => 'required|integer|min:0',
        ]);

        LoanPaymentRule::create($validated);

        return redirect()->route('admin.rules.index')
            ->with('success', 'Payment rule created successfully.');
    }

    
    public function edit(LoanPaymentRule $rule)
    {
        return view('admin.rules.edit', compact('rule'));
    }

    
    public function update(Request $request, LoanPaymentRule $rule)
    {
        $validated = $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'duration_days' => 'required|integer|min:1',
            'installment_type' => 'required|in:SINGLE,WEEKLY,MONTHLY',
            'penalty_type' => 'required|in:FIXED,PERCENTAGE',
            'penalty_value' => 'required|numeric|min:0',
            'grace_period_days' => 'required|integer|min:0',
        ]);

        $rule->update($validated);

        return redirect()->route('admin.rules.index')
            ->with('success', 'Payment rule updated successfully.');
    }

    
    public function destroy(LoanPaymentRule $rule)
    {
        $rule->delete();
        return redirect()->route('admin.rules.index')
            ->with('success', 'Payment rule deleted successfully.');
    }
}
