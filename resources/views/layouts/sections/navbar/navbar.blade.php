@php
    $containerNav = $containerNav ?? 'container-fluid';
    $navbarDetached = $navbarDetached ?? '';
@endphp
<input type="hidden" name="auth_user_type" value="{{ auth()->user()->type }}">
<input type="hidden" name="auth_user_id" value="{{ auth()->user()->id }}">
<!-- Navbar -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
    <nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
        id="layout-navbar">
@endif
@if (isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{ $containerNav }}">
@endif
<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{ url('/') }}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">
                <img src="{{ App\Models\AdminSetting::where('slug', 'site_logo')->first() ? asset(App\Models\AdminSetting::where('slug', 'site_logo')->first()->value) : 'default/logo.png' }}"
                    alt="logo">
            </span>
            <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
        </a>
    </div>
@endif
<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
    <div
        class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>
@endif
<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    @if (!isset($menuHorizontal))
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
                <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
                    <i class="ti ti-search ti-md me-2"></i>
                    <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span>
                </a>
            </div>
        </div>
        <!--👉 /Search -->
    @endif
    <ul class="navbar-nav flex-row align-items-center ms-auto">
        @if (isset($menuHorizontal))
            <!-- Search -->
            <li class="nav-item navbar-search-wrapper me-2 me-xl-0">
                <a class="nav-link search-toggler" href="javascript:void(0);">
                    <i class="ti ti-search ti-md"></i>
                </a>
            </li>
            <!-- 👉 /Search -->
        @endif
        <!-- 👉 Style Switcher -->
        <li class="nav-item me-2 me-xl-0">
            <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                <i class='ti ti-md'></i>
            </a>
        </li>
        <!--👉 Style Switcher -->

        <!-- 👉 Notification -->
        <?php
        
        use App\Models\ManagerBalanceTransferHistory;
        
        if (auth()->user()->type == FRANCHISE_MANAGER) {
            $franchise_manager_panel_balance_history = App\Models\ManagerBalanceHistory::where(['status' => STATUS_PENDING, 'manager_id' => auth()->user()->id])->get();
            $total_notificaion = count($franchise_manager_panel_balance_history);
        } else {
            $b_t_h_reciver = ManagerBalanceTransferHistory::with('sender')
                ->where(['reciver_id' => auth()->user()->id, 'status' => 'pending'])
                ->get();
            $b_t_h_sender = ManagerBalanceTransferHistory::with('receiver')
                ->where(['sender_id' => auth()->user()->id, 'notification_status' => 1])
                ->get();
            $total_notificaion = count($b_t_h_reciver) + count($b_t_h_sender);
        }
        ?>
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
            @if ($total_notificaion > 0)
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-expanded="false">
                    <i class="ti ti-bell ti-md"></i>
                    <span class="badge bg-danger rounded-pill badge-notifications">{{ $total_notificaion }}</span>
                </a>
            @endif
            <ul class="dropdown-menu dropdown-menu-end py-0">
                <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                        <h5 class="text-body mb-0 me-auto">Notification</h5>
                        <a href="javascript:void(0)" class="dropdown-notifications-all text-body"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read"><i
                                class="ti ti-mail-opened fs-4"></i></a>
                    </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush">
                        @if (auth()->user()->type == APP_MANAGER)
                            @foreach ($b_t_h_reciver as $bth_item)
                                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt
                                                    class="h-auto rounded-circle">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Transefer balance 🎉</h6>
                                            <p class="mb-0">{{ $bth_item->sender->name }} transfer you
                                                {{ $bth_item->amount }} TK</p>
                                            <small
                                                class="text-muted">{{ $bth_item->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            <a onclick="return confirm('Are you sure to Accept')"
                                                href="{{ route('accept_transfer_balance', $bth_item->id) }}"
                                                class="dropdown-notifications-read" title="accept"><i
                                                    class="bi bi-check2-circle"></i></a>
                                            <a href="{{ route('view_transfer_balance', $bth_item->id) }}"
                                                class="dropdown-notifications-read" title="view Transfer"><i
                                                    class="bi bi-eye"></i></a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                            @foreach ($b_t_h_sender as $bth_item)
                                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt
                                                    class="h-auto rounded-circle">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 text-capitalize">{{ $bth_item->status }} Transefer balance
                                                🎉</h6>
                                            <p class="mb-0 text-capitalize">{{ $bth_item->receiver->name }}
                                                {{ $bth_item->status }} your Transefer | Amount
                                                {{ $bth_item->amount }} of {{ $bth_item->recived_amount }} Tk </p>
                                            <small
                                                class="text-muted">{{ $bth_item->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            <a href="{{ route('seen_transfer_balance_notification', $bth_item->id) }}"
                                                class="dropdown-notifications-read"
                                                title="Make  Notification as Seen"><i
                                                    class="bi bi-check2-circle"></i></a>
                                            <a href="{{ route('view_transfer_balance', $bth_item->id) }}"
                                                class="dropdown-notifications-read" title="view Transfer"><i
                                                    class="bi bi-eye"></i></a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endif
                        @if (auth()->user()->type == FRANCHISE_MANAGER)
                            @foreach ($franchise_manager_panel_balance_history as $bth_item)
                                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt
                                                    class="h-auto rounded-circle">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 text-capitalize">Add Panel balance 🎉</h6>
                                            <p class="mb-0 text-capitalize"> your have request to add your panel
                                                {{ $bth_item->balance }} Tk of Balance </p>
                                            <small
                                                class="text-muted">{{ $bth_item->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            <a href="{{ route('manager-update-panel-balance', ['id' => $bth_item->id, 'action_for' => STATUS_ACCEPTED]) }}"
                                                class="dropdown-notifications-read badge bg-label-success mb-2"
                                                title="Accept panel Balance">Accept</a>
                                            <a href="{{ route('manager-update-panel-balance', ['id' => $bth_item->id, 'action_for' => STATUS_REJECTED]) }}"
                                                class="dropdown-notifications-read badge bg-label-danger"
                                                title="Rejected panel Balace">Rejected</a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </li>
                <li class="dropdown-menu-footer border-top">
                    <a href="javascript:void(0);"
                        class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center">
                        View all notifications
                    </a>
                </li>
            </ul>
        </li>
        <!--👉 Notification -->

        <!--👉 User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <img src="{{ Auth::user()->profile_photo_url ? asset(Auth::user()->profile_photo_url) : asset(MANAGER_DEFAULT_LOG) }}"
                        alt="" class="h-auto rounded-circle">
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    <img src="{{ Auth::user()->profile_photo_url ? asset(Auth::user()->profile_photo_url) : asset(MANAGER_DEFAULT_LOG) }}"
                                        alt="{{ Auth::user()->profile_photo_url }}" class="h-auto rounded-circle">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <span class="fw-semibold d-block text-capitalize">
                                    @if (Auth::check())
                                        {{ Auth::user()->name }}
                                    @else
                                        John Doe
                                    @endif
                                </span>
                                <small
                                    class="text-muted text-capitalize">{{ str_replace(['[', ']', '"'], '', Auth::user()->roles->pluck('name')) }}</small>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider"></div>
                </li>
                @can('Auth Manager Profile')
                    <li>
                        <a class="dropdown-item" href="{{ route('managerProfile', auth()->user()->id) }}">
                            <i class="ti ti-user-check me-2 ti-sm"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                @endcan
                @can('Auth Manager Clear Cache')
                    <li>
                        <a class="dropdown-item" href="{{ route('rc') }}">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 ti ti-refresh me-2 ti-sm"></i>
                                <span class="flex-grow-1 align-middle">Clear Cache</span>
                            </span> </a>
                    </li>
                @endcan
                @can('Activity Log Auth')
                    <li>
                        <a class="dropdown-item" href="{{ route('log-history.index') }}">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 ti ti-info-circle me-2 ti-sm"></i>
                                <span class="flex-grow-1 align-middle">Logs history</span>
                            </span> </a>
                    </li>
                @endcan
                @can('Activity Log All')
                    <li>
                        <a class="dropdown-item" href="{{ route('log-history.store') }}">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 ti ti-info-square me-2 ti-sm"></i>
                                <span class="flex-grow-1 align-middle">All Logs History</span>
                            </span> </a>
                    </li>
                @endcan
                <!-- <li>
                <a onclick="return confirm('Are you Sure to Run This Command')" class="dropdown-item" href="{{ route('user-disconnect-expired-customer') }}">
                  <span class="d-flex align-items-center align-middle">
                    <i class="flex-shrink-0 ti ti-refresh-alert me-2 ti-sm"></i>
                    <span class="flex-grow-1 align-middle">User disconnect</span>
                  </span> </a>
              </li> -->
                <!-- @can('Auth Manager User Disconnect')
@endcan -->
                <li>
                    <div class="dropdown-divider"></div>
                </li>
                @if (Auth::check())
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class='ti ti-logout me-2'></i>
                            <span class="align-middle">Logout</span>
                        </a>
                    </li>
                    <form method="POST" id="logout-form" action="{{ route('logout') }}">
                        @csrf
                    </form>
                @else
                    <li>
                        <a class="dropdown-item"
                            href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                            <i class='ti ti-login me-2'></i>
                            <span class="align-middle">Login</span>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>

<!-- Search Small Screens -->
<div class="navbar-search-wrapper search-input-wrapper {{ isset($menuHorizontal) ? $containerNav : '' }} d-none">
    <input type="text"
        class="form-control search-input {{ isset($menuHorizontal) ? '' : $containerNav }} border-0"
        placeholder="Search..." aria-label="Search...">
    <i class="ti ti-x ti-sm search-toggler cursor-pointer"></i>
</div>
@if (isset($navbarDetached) && $navbarDetached == '')
    </div>
@endif
</nav>
<!-- / Navbar -->
