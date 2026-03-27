@extends('admin.layout')
@section('admin-rule-edit-content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Edit Loan Payment Rule</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0 mt-3">
                        <form method="post" action="{{ route('admin.rules.update', $rule->id) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Min Amount ($)</label>
                                <div class="col-sm-10">
                                    <input type="number" step="0.01" class="form-control" name="min_amount" value="{{ old('min_amount', $rule->min_amount) }}">
                                    @error('min_amount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Max Amount ($)</label>
                                <div class="col-sm-10">
                                    <input type="number" step="0.01" class="form-control" name="max_amount" value="{{ old('max_amount', $rule->max_amount) }}">
                                    @error('max_amount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Duration (Days)</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="duration_days" value="{{ old('duration_days', $rule->duration_days) }}">
                                    @error('duration_days') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Installment Type</label>
                                <div class="col-sm-10">
                                    <select class="form-select" name="installment_type">
                                        <option value="SINGLE" {{ $rule->installment_type == 'SINGLE' ? 'selected' : '' }}>SINGLE (Pay at once)</option>
                                        <option value="WEEKLY" {{ $rule->installment_type == 'WEEKLY' ? 'selected' : '' }}>WEEKLY</option>
                                        <option value="MONTHLY" {{ $rule->installment_type == 'MONTHLY' ? 'selected' : '' }}>MONTHLY</option>
                                    </select>
                                    @error('installment_type') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Penalty Type</label>
                                <div class="col-sm-10">
                                    <select class="form-select" name="penalty_type">
                                        <option value="FIXED" {{ $rule->penalty_type == 'FIXED' ? 'selected' : '' }}>FIXED Amount ($)</option>
                                        <option value="PERCENTAGE" {{ $rule->penalty_type == 'PERCENTAGE' ? 'selected' : '' }}>PERCENTAGE (%)</option>
                                    </select>
                                    @error('penalty_type') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Penalty Value</label>
                                <div class="col-sm-10">
                                    <input type="number" step="0.01" class="form-control" name="penalty_value" value="{{ old('penalty_value', $rule->penalty_value) }}">
                                    @error('penalty_value') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Grace Period (Days)</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="grace_period_days" value="{{ old('grace_period_days', $rule->grace_period_days) }}">
                                    @error('grace_period_days') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-10 ms-auto">
                                    <button type="submit" class="btn btn-primary">Update Rule</button>
                                    <a href="{{ route('admin.rules.index') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection