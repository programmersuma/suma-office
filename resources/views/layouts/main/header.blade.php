<div id="kt_header" style="" class="header align-items-stretch">
    <!--begin::Container-->
    <div class="container-fluid d-flex align-items-stretch justify-content-between">
        <!--begin::Aside mobile toggle-->
        <div class="d-flex align-items-center d-lg-none ms-n2 me-2" title="Show aside menu">
            <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" id="kt_aside_mobile_toggle">
                <span class="svg-icon svg-icon-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="currentColor" />
                        <path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="currentColor" />
                    </svg>
                </span>
            </div>
        </div>
        <!--begin::Mobile logo-->
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="#" class="d-lg-none">
                <img alt="Logo" src="{{ asset('assets/images/logo/logo_suma_bg_white.svg') }}" class="h-50px" />
            </a>
        </div>
        <!--begin::Wrapper-->
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
            <!--begin::Navbar-->
            <div class="d-flex align-items-center" id="kt_header_nav">
                <!--begin::Page title-->
                <div data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_header_nav'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0" id="kt_header_title">
                    <!--begin::Title-->
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Suma</h1>
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <li class="breadcrumb-item text-muted">@yield('title')</li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">@yield('subtitle')</li>
                    </ul>
                    <!--end::Page title-->
                </div>
            </div>

            <!--begin::Toolbar wrapper-->
            <div class="d-flex align-items-stretch flex-shrink-0">
                <div class="d-flex align-items-stretch flex-shrink-0">

                    <div class="d-flex align-items-center" data-kt-search-element="toggle" id="kt_header_search_toggle" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <div class="btn btn-icon btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px">
                            <span class="svg-icon svg-icon-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>
                                    <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"></path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="menu menu-sub menu-sub-dropdown p-7 w-325px w-md-375px" data-kt-menu="true" id="kt_menu_6244763d95a3a" style="">
                        <div data-kt-search-element="wrapper">
                            <form data-kt-search-element="form" class="w-100 position-relative mb-3" action="{{ route('parts.partnumber.daftar') }}" method="get" autocomplete="off">
                                <span class="svg-icon svg-icon-2 svg-icon-lg-1 svg-icon-gray-500 position-absolute top-50 translate-middle-y ms-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>
                                        <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                                <input id="searchHeaderParts" type="search" class="search-input form-control form-control-flush ps-10" name="part_number" value="" placeholder="Cari Part Number" data-kt-search-element="input">
                            </form>
                        </div>
                    </div>

                    <!--begin::Chat-->
                    <div class="d-flex align-items-center" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <!--begin::Menu wrapper-->
                        <a class="btn btn-icon btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px" href="{{ url('/notifikasi/form') }}">
                            <!--begin::Svg Icon | path: icons/duotune/communication/com012.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path opacity="0.3" d="M20 3H4C2.89543 3 2 3.89543 2 5V16C2 17.1046 2.89543 18 4 18H4.5C5.05228 18 5.5 18.4477 5.5 19V21.5052C5.5 22.1441 6.21212 22.5253 6.74376 22.1708L11.4885 19.0077C12.4741 18.3506 13.6321 18 14.8167 18H20C21.1046 18 22 17.1046 22 16V5C22 3.89543 21.1046 3 20 3Z" fill="black" />
                                    <rect x="6" y="12" width="7" height="2" rx="1" fill="black" />
                                    <rect x="6" y="7" width="12" height="2" rx="1" fill="black" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </a>
                        <!--end::Menu wrapper-->
                    </div>
                    <!--end::Chat-->

                    <!--begin::User menu-->
                    <div class="d-flex align-items-center ms-1 ms-lg-6" id="kt_header_user_menu_toggle">
                        <div class="cursor-pointer symbol symbol-30px symbol-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                            <img src="{{ Session::get('app_user_photo') }}" alt="user" />
                        </div>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-275px" data-kt-menu="true">
                            <div class="menu-item px-3">
                                <div class="menu-content d-flex align-items-center px-3">
                                    <div class="symbol symbol-50px me-5">
                                        <img alt="Logo" src="{{ Session::get('app_user_photo') }}" />
                                    </div>
                                    <div class="d-flex flex-column">
                                        <div class="fw-bolder d-flex align-items-center fs-5">{{ Session::get('app_user_name') }}
                                            <span class="badge badge-light-success fw-bolder fs-8 px-2 py-1 ms-2">{{ Session::get('app_user_role_id') }}</span>
                                        </div>
                                        <a href="#" class="fw-bold text-muted text-hover-primary fs-7">{{ Session::get('app_user_email') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="separator my-2"></div>
                            <div class="menu-item px-5">
                                <a href="{{ route('profile.account.index') }}" class="menu-link {{ (str_contains($title_menu, 'My Account')) ? 'active' : '' }} px-5">My Account</a>
                            </div>
                            <div class="menu-item px-5">
                                <a href="{{ route('orders.cart.index') }}" class="menu-link {{ (str_contains($title_menu, 'Cart')) ? 'active' : '' }} px-5">My Orders</a>
                            </div>
                            <div class="separator my-2"></div>
                            <div class="menu-item px-5" data-kt-menu-trigger="hover" data-kt-menu-placement="left-start">
                                <span class="menu-link px-5">
                                    <span class="menu-title">Authorization</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-dropdown w-175px py-4" style="">
                                    <div class="menu-item px-3">
                                        <a href="{{ route('online.auth.shopee.auth') }}" class="menu-link px-5">Shopee</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <span class="menu-link px-5">Lazada</span>
                                    </div>
                                    <div class="menu-item px-3">
                                        <span class="menu-link px-5">Tiktok</span>
                                    </div>
                                </div>
                            </div>
                            <div class="separator my-2"></div>
                            <div class="menu-item px-5">
                                <a href="{{ route('profile.account.change-password') }}" class="menu-link {{ (str_contains($title_menu, 'Change Password')) ? 'active' : '' }}">Change Password</a>
                            </div>
                            <div class="separator my-2"></div>
                            <div class="menu-item px-5">
                                <a href="{{ route('auth.logout') }}" class="menu-link px-5">Sign Out</a>
                            </div>
                        </div>
                    </div>
                    <!--begin::Header menu toggle-->
                    <!--end::Header menu toggle-->
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Header-->
