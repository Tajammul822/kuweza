<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>Kuweza | Kuweza - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Kuweza BNPL Platform" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->

    <link rel="shortcut icon" href="{{ url('assets/images/favicon.ico') }}">
    <link rel="stylesheet" href="{{ url('assets/css/jsvectormap.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App css -->
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ url('assets/css/icons.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ url('assets/css/app.min.css') }}" type="text/css" />

</head>

<body>

    <!-- Top Bar Start -->
    <div class="topbar d-print-none">
        <div class="container-xxl">
            <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">


                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li>
                        <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                            <i class="iconoir-menu-scale"></i>
                        </button>
                    </li>
                    <li class="mx-3 welcome-text">
                        <h3 class="mb-0 fw-bold text-truncate">Welcome {{ auth()->user()->name ?? 'Add a name' }}</h3>
                    </li>
                </ul>
                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    {{-- <li class="topbar-item">
                        <a class="nav-link nav-icon" href="javascript:void(0);" id="light-dark-mode">
                            <i class="icofont-moon dark-mode"></i>
                            <i class="icofont-sun light-mode"></i>
                        </a>
                    </li> --}}

                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <img src="{{ url('assets/images/user-avatar.jpg') }}" alt=""
                                class="thumb-lg rounded-circle">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end py-0">
                            <div class="d-flex align-items-center dropdown-item py-2 bg-secondary-subtle">
                                <div class="flex-shrink-0">
                                    <img src="{{ url('assets/images/user-avatar.jpg') }}" alt=""
                                        class="thumb-md rounded-circle">
                                </div>
                                <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                    <h6 class="my-0 fw-medium text-dark fs-13">{{ auth()->user()->name }}</h6>
                                    <small
                                        class="text-muted mb-0">Admin</small>
                                </div><!--end media-body-->
                            </div>
                            <div class="dropdown-divider mt-0"></div>
                            <small class="text-muted px-2 pb-1 d-block">Account</small>
                            <a class="dropdown-item" href=""><i
                                    class="las la-user fs-18 me-1 align-text-bottom"></i> Profile</a>
                            <div class="dropdown-divider mb-0"></div>
                            {{-- <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="las la-power-off fs-18 me-1 align-text-bottom"></i> Logout
                                </button>
                            </form> --}}
                        </div>
                    </li>
                </ul><!--end topbar-nav-->
            </nav>
            <!-- end navbar-->
        </div>
    </div>
    <!-- Top Bar End -->
    <!-- leftbar-tab-menu -->
    <div class="startbar d-print-none">
        <!--start brand-->
        <div class="brand">
            <a href="{{ route('admin.dashboard') }}" class="logo">
                <span>
                    <img src="{{ url('assets/images/admin-logo.jpg') }}" alt="logo" class="logo-sm"
                        style="width:200px; height:100px; margin-right:50px;">
                </span>
            </a>
        </div>
        <!--end brand-->
        <!--start startbar-menu-->
        <div class="startbar-menu">
            <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
                <div class="d-flex align-items-start flex-column w-100">
                    <!-- Navigation -->
                    <ul class="navbar-nav mb-auto w-100">
                        <li class="menu-label pt-0 mt-0">
                            <span>Main Menu</span>
                        </li>

                        <!-- Dashboards -->
                       
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                    href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                                    aria-expanded="{{ request()->routeIs('admin.dashboard') ? 'true' : 'false' }}"
                                    aria-controls="sidebarDashboards">
                                    <i class="iconoir-home-simple menu-icon"></i>
                                    <span>Dashboards</span>
                                </a>
                                <div class="collapse {{ request()->routeIs('admin.dashboard') ? 'show' : '' }}"
                                    id="sidebarDashboards">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                                href="{{ route('admin.dashboard') }}">Analytics</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <!-- Users -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.index') || request()->routeIs('users.create') ? 'active' : '' }}"
                                    href="#sidebarApplications" data-bs-toggle="collapse" role="button"
                                    aria-expanded="{{ request()->routeIs('users.index') || request()->routeIs('users.create') ? 'true' : 'false' }}"
                                    aria-controls="sidebarApplications">
                                    <i class="fas fa-id-badge menu-icon"></i>
                                    <span>Users</span>
                                </a>
                                <div class="collapse {{ request()->routeIs('users.index') || request()->routeIs('users.create') ? 'show' : '' }}"
                                    id="sidebarApplications">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}"
                                                href="{{ route('users.index') }}">Users</a>
                                        </li>
                                        {{-- <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}"
                                                href="{{ route('users.create') }}">Add User</a>
                                        </li> --}}
                                    </ul>
                                </div>
                            </li>
                        
                        <!-- Payment Rules -->
                        <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.rules.*') ? 'active' : '' }}"
        href="#sidebarRules" data-bs-toggle="collapse" role="button"
        aria-expanded="{{ request()->routeIs('admin.rules.*') ? 'true' : 'false' }}"
        aria-controls="sidebarRules">
        <i class="fas fa-file-invoice-dollar menu-icon"></i>
        <span>Payment Rules</span>
    </a>
    <div class="collapse {{ request()->routeIs('admin.rules.*') ? 'show' : '' }}"
        id="sidebarRules">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.rules.index') ? 'active' : '' }}"
                    href="{{ route('admin.rules.index') }}">All Rules</a>
            </li>
            @if (auth()->check() && auth()->user()->roles_id == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.rules.create') ? 'active' : '' }}" 
                        href="{{ route('admin.rules.create') }}">Add Rule</a>
                </li>
            @endif
        </ul>
    </div>
</li>

                        <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}"
        href="#sidebarTransactions" data-bs-toggle="collapse" role="button"
        aria-expanded="{{ request()->routeIs('admin.transactions.*') ? 'true' : 'false' }}"
        aria-controls="sidebarTransactions">
        <i class="fas fa-exchange-alt menu-icon"></i>
        <span>Transactions</span>
    </a>
    <div class="collapse {{ request()->routeIs('admin.transactions.*') ? 'show' : '' }}"
        id="sidebarTransactions">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.transactions.index') ? 'active' : '' }}"
                    href="{{ route('admin.transactions.index') }}">All Transactions</a>
            </li>
        </ul>
    </div>
</li>

                        <!-- Logs -->
                        {{-- @if (auth()->check() && auth()->user()->roles_id == 1)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('view.files') ? 'active' : '' }}"
                                    href="#sidebarLogs" data-bs-toggle="collapse" role="button"
                                    aria-expanded="{{ request()->routeIs('view.files') ? 'true' : 'false' }}"
                                    aria-controls="sidebarLogs">
                                    <i class="fas fa-history menu-icon"></i>
                                    <span>Logs</span>
                                </a>
                                <div class="collapse {{ request()->routeIs('view.files') ? 'show' : '' }}"
                                    id="sidebarLogs">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('view.files') ? 'active' : '' }}"
                                                href="{{ route('view.files') }}">View Logs</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.request') ? 'active' : '' }}"
                                    href="#sidebarRequest" data-bs-toggle="collapse" role="button"
                                    aria-expanded="{{ request()->routeIs('admin.request') ? 'true' : 'false' }}"
                                    aria-controls="sidebarRequest">
                                    <i class="fas fa-universal-access menu-icon"></i>
                                    <span>Requests</span>
                                </a>
                                <div class="collapse {{ request()->routeIs('admin.request') ? 'show' : '' }}"
                                    id="sidebarRequest">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('admin.request') ? 'active' : '' }}"
                                                href="{{ route('admin.request') }}">View Requests</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        @endif --}}
                    </ul>
                </div>
            </div>
        </div>
        <!--end startbar-menu-->
    </div><!--end startbar-->
    <div class="startbar-overlay d-print-none"></div>
    <!-- end leftbar-tab-menu-->

    <div class="page-wrapper">
        <div class="page-content">
            @yield('admin-dasboard-content')
            @yield('admin-user-index-content')
            @yield('admin-rule-content')
            @yield('admin-rule-create-content')
             @yield('admin-rule-edit-content')
             @yield('admin-transaction-index-content')
             @yield('admin-transaction-detail-content')

            <footer class="footer text-center text-sm-start d-print-none">
                <div class="container-xxl">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-0 rounded-bottom-0">
                                <div class="card-body">
                                    <p class="text-muted mb-0">
                                        ©
                                        <script>
                                            document.write(new Date().getFullYear())
                                        </script>
                                        Kuweza
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>


    <!-- end page-wrapper -->

    <!-- Javascript  -->
    <!-- vendor js -->

    <script src="{{ url('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('assets/js/simplebar.min.js') }}"></script>
    @if (!request()->is('admin/deal-requests') && !request()->is('admin/users'))
        <script src="{{ url('assets/js/simple-datatables.js') }}"></script>
        <script src="{{ url('assets/js/datatable.init.js') }}"></script>
    @endif
    <script src="{{ url('assets/js/apexcharts.min.js') }}"></script>
    <script src="{{ url('assets/js/stock-prices.js') }}"></script>
    <script src="{{ url('assets/js/jsvectormap.min.js') }}"></script>
    <script src="{{ url('assets/js/world.js') }}"></script>
    <script src="{{ url('assets/js/index.init.js') }}"></script>
    <script src="{{ url('assets/js/app.js') }}"></script>
    <script src="{{ url('assets/js/form-validation.js') }}"></script>

</body>
<!--end body-->

</html>
