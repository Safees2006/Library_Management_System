<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'Library') | The Reading Room</title>
    <style>:root { --library-background-image: url("{{ asset('images/library-background.jpg') }}"); }</style>
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/css/book-covers.css', 'resources/css/admin-layout.css', 'resources/css/global-theme.css', 'resources/js/app.js'])
</head>
<body>
    @if (! request()->routeIs('login') && auth()->check() && auth()->user()->isAdmin())
        <div class="admin-layout d-flex min-vh-100" id="admin-layout">
            <aside class="admin-sidebar d-flex flex-column" id="admin-sidebar" aria-label="Admin navigation">
                <a class="admin-brand" href="{{ route('login') }}" aria-label="Go to sign in">
                    <span class="admin-brand-mark" aria-hidden="true">𝕽</span>
                    <span class="admin-brand-name"><strong>The Reading Room</strong><small>LIBRARY MANAGEMENT</small></span>
                </a>

                <a class="admin-create-button" href="{{ route('admin.loans.create') }}"><i class="bi bi-journal-plus" aria-hidden="true"></i><span class="admin-create-label">ISSUE A BOOK</span></a>

                <span class="admin-menu-label">MENU</span>
                <nav class="admin-navigation">
                    <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.dashboard')])>
                        <i class="bi bi-speedometer2" aria-hidden="true"></i><span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.books.index') }}" aria-label="Books" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.books.*')])>
                        <i class="bi bi-journal-bookmark" aria-hidden="true"></i><span>Books</span>
                    </a>
                    <a href="{{ route('admin.members.index') }}" aria-label="Members" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.members.*')])>
                        <i class="bi bi-people" aria-hidden="true"></i><span>Members</span>
                    </a>
                    <a href="{{ route('admin.loans.index') }}" aria-label="Loans and returns" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.loans.*')])>
                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i><span>Loans &amp; returns</span>
                    </a>
                    <a href="{{ route('admin.catalog-data.index') }}" aria-label="Authors and categories" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.catalog-data.*', 'admin.categories.*', 'admin.authors.*')])>
                        <i class="bi bi-tags" aria-hidden="true"></i><span>Authors &amp; categories</span>
                    </a>
                    <a href="{{ route('admin.fines.index') }}" aria-label="Fines and payments" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.fines.*')])>
                        <i class="bi bi-cash-coin" aria-hidden="true"></i><span>Fines &amp; payments</span>
                    </a>
                    <a href="{{ route('catalog') }}" class="admin-nav-link" aria-label="Public catalog" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span>Public catalog</span>
                    </a>
                </nav>

                <div class="admin-sidebar-bottom">
                    <div class="admin-user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="admin-user-copy"><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></div>
                    <form action="{{ route('logout') }}" method="POST" class="admin-signout-form">@csrf<button type="submit" aria-label="Sign out" title="Sign out"><i class="bi bi-box-arrow-right" aria-hidden="true"></i></button></form>
                </div>
            </aside>

            <button class="admin-sidebar-backdrop" type="button" aria-label="Close navigation" data-admin-backdrop></button>

            <div class="admin-main flex-grow-1">
                <header class="admin-topbar navbar">
                    <div class="admin-topbar-left">
                        <button class="admin-sidebar-toggle" type="button" aria-label="Collapse navigation" aria-expanded="true" data-admin-toggle><i class="bi bi-chevron-double-left" aria-hidden="true"></i></button>
                        <span class="admin-topbar-divider" aria-hidden="true"></span>
                        <span class="admin-current-page">@yield('title', 'Dashboard')</span>
                    </div>
                    <div class="admin-topbar-right">
                        <button class="admin-topbar-action" type="button" aria-label="Search" title="Search" data-tooltip="Search" aria-haspopup="dialog" aria-controls="admin-search-dialog" aria-expanded="false" data-admin-search-toggle><i class="bi bi-search" aria-hidden="true"></i></button>
                        <div class="admin-topbar-tool" data-admin-topbar-tool>
                            <button class="admin-topbar-action" type="button" aria-label="Notifications" title="Notifications" data-tooltip="Notifications" aria-haspopup="true" aria-controls="admin-notifications-panel" aria-expanded="false" data-admin-panel-toggle><i class="bi bi-bell" aria-hidden="true"></i></button>
                            <section class="admin-action-popover admin-notifications-popover" id="admin-notifications-panel" aria-label="Notifications" hidden>
                                <div class="admin-popover-heading"><div><strong>Notifications</strong><small>Library updates</small></div><span class="admin-popover-count">0 new</span></div>
                                <div class="admin-notification-empty"><span class="admin-notification-icon" aria-hidden="true"><i class="bi bi-bell" aria-hidden="true"></i></span><strong>You're all caught up</strong><p>New loan and library updates will appear here.</p></div>
                                <a class="admin-popover-footer" href="{{ route('admin.loans.index') }}">View loans and returns <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                            </section>
                        </div>
                        <div class="admin-topbar-tool" data-admin-topbar-tool>
                            <button class="admin-topbar-action" type="button" aria-label="Settings" title="Settings" data-tooltip="Settings" aria-haspopup="true" aria-controls="admin-settings-panel" aria-expanded="false" data-admin-panel-toggle><i class="bi bi-gear" aria-hidden="true"></i></button>
                            <section class="admin-action-popover admin-settings-popover" id="admin-settings-panel" aria-label="Library management options" hidden>
                                <nav class="admin-settings-options" aria-label="Library management shortcuts">
                                    <a href="{{ route('admin.dashboard') }}">Library Overview</a>
                                    <a href="{{ route('admin.catalog-data.index') }}">Catalog Setup</a>
                                    <a href="{{ route('admin.books.index') }}">Book Collection</a>
                                    <a href="{{ route('admin.members.index') }}">Member Accounts</a>
                                    <a href="{{ route('admin.loans.index') }}">Loans &amp; Returns</a>
                                    <a href="{{ route('admin.fines.index') }}">Fines &amp; Payments</a>
                                    <a href="{{ route('admin.books.create') }}">Add a Book</a>
                                </nav>
                            </section>
                        </div>
                        <div class="admin-topbar-tool" data-admin-topbar-tool>
                            <button class="admin-topbar-avatar" type="button" aria-label="Open account menu" title="{{ auth()->user()->name }}" aria-haspopup="true" aria-controls="admin-account-panel" aria-expanded="false" data-admin-panel-toggle>{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</button>
                            <section class="admin-action-popover admin-account-popover" id="admin-account-panel" aria-label="Account menu" hidden>
                                <div class="admin-account-heading">
                                    <strong>{{ auth()->user()->name }}</strong>
                                    <small>{{ auth()->user()->email }}</small>
                                </div>
                                <nav class="admin-account-options" aria-label="Account options">
                                    <a href="{{ route('admin.profile') }}">My Profile</a>
                                    <a href="{{ route('catalog') }}" target="_blank" rel="noopener">Public Catalog</a>
                                </nav>
                                <form action="{{ route('logout') }}" method="POST" class="admin-account-logout-form">
                                    @csrf
                                    <button type="submit">Log Out</button>
                                </form>
                            </section>
                        </div>
                    </div>
                </header>

                <div class="admin-search-overlay" id="admin-search-dialog" role="dialog" aria-modal="true" aria-labelledby="admin-search-title" hidden data-admin-search-dialog>
                    <section class="admin-search-dialog">
                        <form class="admin-global-search-form" action="{{ route('admin.books.index') }}" method="GET" role="search">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <label class="visually-hidden" id="admin-search-title" for="admin-global-search">Search library books</label>
                            <input id="admin-global-search" type="search" name="q" placeholder="Search books, authors, or ISBNs…" autocomplete="off" required minlength="2" maxlength="100" data-admin-search-input>
                            <button class="admin-search-close" type="button" aria-label="Close search" data-admin-search-close><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </form>
                        <div class="admin-search-empty"><span class="admin-search-empty-icon" aria-hidden="true"><i class="bi bi-search" aria-hidden="true"></i></span><strong>Search the library catalog</strong><p>Find a book by title, author, or ISBN.</p></div>
                        <div class="admin-search-hints"><span><kbd>Enter</kbd> search books</span><span><kbd>Esc</kbd> close</span></div>
                    </section>
                </div>

                <main class="admin-page-shell container-fluid">
                    @if (session('status'))<div class="flash flash-success" role="status">{{ session('status') }}</div>@endif
                    @if ($errors->any())<div class="flash flash-error" role="alert"><strong>Please check the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <header class="site-header navbar navbar-expand-md">
            <a class="brand" href="{{ route('login') }}" aria-label="The Reading Room sign in">
                <span class="brand-mark">R</span>
                <span><strong>The Reading Room</strong><small>Community Library</small></span>
            </a>
            <button class="nav-toggle btn btn-outline-success navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-navigation" aria-expanded="false" aria-controls="main-navigation"><i class="bi bi-list" aria-hidden="true"></i><span class="visually-hidden">Menu</span></button>
            <nav class="main-nav collapse navbar-collapse" id="main-navigation">
                <a href="{{ route('catalog') }}" @class(['is-current' => request()->routeIs('catalog*')])>Explore books</a>
                @auth
                    @if (! auth()->user()->isAdmin())
                        <a href="{{ route('member.account') }}" @class(['is-current' => request()->routeIs('member.*')])>My account</a>
                    @endif
                    <span class="nav-user">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="nav-logout">@csrf<button class="btn button button-quiet button-small" type="submit">Sign out <i class="bi bi-box-arrow-right" aria-hidden="true"></i></button></form>
                @else
                    <a class="btn btn-success button button-dark button-small" href="{{ route('login') }}">Member sign in <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i></a>
                @endauth
            </nav>
        </header>

        <main class="page-shell container-fluid">
            @if (session('status'))<div class="flash flash-success" role="status">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="flash flash-error" role="alert"><strong>Please check the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>

        <footer class="site-footer"><span>Good stories are meant to be shared.</span><span>Library services &middot; Open to everyone</span></footer>
    @endif
</body>
</html>
