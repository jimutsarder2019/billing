@php
    $configData = Helper::appClasses();
	if(auth()->user()->type == 'user'){
		$menuGenerateData = $menuData[2];
	}else{
		$menuGenerateData = $menuData[0];
	}
	print_r($menuGenerateData);
	print auth()->user()->type;
	die;
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- ! Hide app brand if navbar-full -->
    @if (!isset($navbarFull))
        <div class="app-brand demo">
            <a href="{{ url('/') }}" class="app-brand-link">
                <span class="app-brand-logo demo">
                    <img src="{{ asset(App\Models\AdminSetting::where('slug', 'site_logo')->first() ? asset(App\Models\AdminSetting::where('slug', 'site_logo')->first()->value) : 'default/logo.png') }}"
                        alt="logo">
                </span>
                <span
                    class="app-brand-text demo menu-text fw-bold">{{ App\Models\AdminSetting::where('slug', 'site_name')->first() ? App\Models\AdminSetting::where('slug', 'site_name')->first()->value : config('variables.templateName') }}</span>
            </a>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
                <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
            </a>
        </div>
    @endif


    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuGenerateData->menu as $menu)
            {{-- adding active and open class if child is active --}}

            {{-- menu headers --}}
            @if (isset($menu->menuHeader))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ $menu->menuHeader }}</span>
                </li>
            @else
                {{-- active menu method --}}
                @php

                    $path = request()->path();
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName();
                    if ($currentRouteName === $menu->slug) {
                        $activeClass = 'active';
                    } elseif (isset($menu->submenu)) {
                        if (gettype($menu->slug) === 'array') {
                            foreach ($menu->slug as $slug) {
                                if (
                                    str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0 or
                                    str_contains($path, $slug) and strpos($path, $slug) === 0
                                ) {
                                    $activeClass = 'active open';
                                }
                            }
                        } else {
                            if (
                                str_contains($currentRouteName, $menu->slug) and
                                    strpos($currentRouteName, $menu->slug) === 0 or
                                str_contains($path, $menu->slug) and strpos($path, $menu->slug) === 0
                            ) {
                                $activeClass = 'active open';
                            }
                        }
                    }
                @endphp

                {{-- main menu --}}
                @can($menu->permission)
                    <li class="menu-item {{ $activeClass }}">
                        <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                            class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                            @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                            @isset($menu->icon)
                                <i class="{{ $menu->icon }}"></i>
                            @endisset
                            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                            @isset($menu->badge)
                                <div class="badge bg-label-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}
                                </div>
                            @endisset
                        </a>
                        {{-- submenu --}}
                        @isset($menu->submenu)
                            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                        @endisset
                    </li>
                @endcan
            @endif
        @endforeach
    </ul>
</aside>
