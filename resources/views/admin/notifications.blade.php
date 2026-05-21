@extends('admin.layout')
@section('admin-notifications-content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-bell me-2"></i>Notifications</h4>
                    <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-check-double me-1"></i> Mark All Read
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-12">
                @if ($notifications->isEmpty())
                    <div class="card">
                        <div class="card-body text-center py-5 text-muted">
                            <i class="fas fa-bell-slash fs-1 mb-3 d-block"></i>
                            No notifications yet.
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body p-0">
                            @foreach ($notifications as $notification)
                                @php $data = $notification->data; @endphp
                                <div class="d-flex align-items-start p-3 border-bottom {{ is_null($notification->read_at) ? 'bg-light' : '' }}">
                                    <div class="flex-shrink-0 me-3">
                                        @if (($data['type'] ?? '') === 'NEW_TRANSACTION')
                                            <span class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px">
                                                <i class="fas fa-plus"></i>
                                            </span>
                                        @elseif (($data['type'] ?? '') === 'REPAYMENT_RECEIVED')
                                            <span class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </span>
                                        @else
                                            <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px">
                                                <i class="fas fa-info"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 {{ is_null($notification->read_at) ? 'fw-bold' : '' }}">
                                            {{ $data['message'] ?? 'Notification' }}
                                        </p>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        @if (!empty($data['transaction_code']))
                                            <a href="{{ route('admin.transactions.show', $data['transaction_id'] ?? 0) }}"
                                               class="ms-2 badge bg-primary text-decoration-none">
                                                {{ $data['transaction_code'] }}
                                            </a>
                                        @endif
                                    </div>
                                    @if (is_null($notification->read_at))
                                        <span class="badge bg-warning text-dark ms-2 align-self-center">New</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-3">{{ $notifications->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
