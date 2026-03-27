@extends('admin.layout')
@section('admin-rule-content')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <div class="container-xxl">
        @if (Session::get('success'))
            <div class="alert alert-success">
                {{ Session::get('success') }}
            </div>
        @endif
        @if (Session::get('error'))
            <div class="alert alert-danger">
                {{ Session::get('error') }}
            </div>
        @endif
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Loan Payment Rules</h4>
                                <a href="{{ route('admin.rules.create') }}">
                                    <button type="button" class="btn btn-info">Create Rule</button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0 mt-3">
                        <div class="table-responsive">
                            <table class="table datatable" id="rules-index-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Amount Range</th>
                                        <th>Duration</th>
                                        <th>Installment Type</th>
                                        <th>Penalty</th>
                                        <th>Grace Period</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rules as $rule)
                                        <tr>
                                            <td>{{ $rule->id }}</td>
                                            <td>${{ $rule->min_amount }} - ${{ $rule->max_amount }}</td>
                                            <td>{{ $rule->duration_days }} Days</td>
                                            <td><span class="badge bg-primary">{{ $rule->installment_type }}</span></td>
                                            <td>
                                                {{ $rule->penalty_value }} 
                                                <span class="badge bg-warning text-dark">{{ $rule->penalty_type }}</span>
                                            </td>
                                            <td>{{ $rule->grace_period_days }} Days</td>
                                            <td>
                                                <a href="{{ route('admin.rules.edit', $rule->id) }}"
                                                    class="btn btn-primary btn-sm"
                                                    style="height:30px;width:30px;border-radius:50%" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.rules.destroy', $rule->id) }}"
                                                    class="d-inline-block individualDeleteForm">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm dltBtn"
                                                        style="height:30px;width:30px;border-radius:50%" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#rules-index-table').DataTable({
                pageLength: 100
            });

            // SweetAlert for Deletion
            $('.dltBtn').on('click', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, this rule cannot be recovered!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection