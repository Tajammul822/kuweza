@extends('admin.layout')
@section('admin-rule-create-content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Create Loan Payment Rule</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0 mt-3">
                        <form method="post" action="{{ route('admin.rules.store') }}">
                            @csrf
                            
                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Min Amount ($)</label>
                                <div class="col-sm-10">
                                    <input type="number" step="0.01" class="form-control" name="min_amount" placeholder="e.g. 1.00" value="{{ old('min_amount') }}">
                                    @error('min_amount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Max Amount ($)</label>
                                <div class="col-sm-10">
                                    <input type="number" step="0.01" class="form-control" name="max_amount" placeholder="e.g. 100.00" value="{{ old('max_amount') }}">
                                    @error('max_amount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Duration (Days)</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="duration_days" placeholder="e.g. 7" value="{{ old('duration_days') }}">
                                    @error('duration_days') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Installment Type</label>
                                <div class="col-sm-10">
                                    <select class="form-select" name="installment_type">
                                        <option value="SINGLE">SINGLE (Pay at once)</option>
                                        <option value="WEEKLY">WEEKLY</option>
                                        <option value="MONTHLY">MONTHLY</option>
                                    </select>
                                    @error('installment_type') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Penalty Type</label>
                                <div class="col-sm-10">
                                    <select class="form-select" name="penalty_type">
                                        <option value="FIXED">FIXED Amount ($)</option>
                                        <option value="PERCENTAGE">PERCENTAGE (%)</option>
                                    </select>
                                    @error('penalty_type') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Penalty Value</label>
                                <div class="col-sm-10">
                                    <input type="number" step="0.01" class="form-control" name="penalty_value" placeholder="e.g. 5.00" value="{{ old('penalty_value') }}">
                                    @error('penalty_value') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-sm-2 col-form-label">Grace Period (Days)</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="grace_period_days" placeholder="e.g. 1" value="{{ old('grace_period_days', 0) }}">
                                    @error('grace_period_days') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-10 ms-auto">
                                    <button type="submit" class="btn btn-primary">Save Rule</button>
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