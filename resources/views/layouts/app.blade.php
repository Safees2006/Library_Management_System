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
                    <span class="admin-brand-mark" aria-hidden="true">R</span>
                    <span class="admin-brand-name"><strong>The Reading Room</strong><small>LIBRARY MANAGEMENT</small></span>
                </a>

                <a class="admin-create-button" href="{{ route('admin.loans.create') }}"><span aria-hidden="true">+</span><span class="admin-create-label">ISSUE A BOOK</span></a>

                <span class="admin-menu-label">MENU</span>
                <nav class="admin-navigation">
                    <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.dashboard')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3.5 10 8.5-7 8.5 7v10.5h-6v-7h-5v7h-6V10Z"/></svg><span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.books.index') }}" aria-label="Books" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.books.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5c0-1.1.9-2 2-2h12M9 8h6M9 11h6"/></svg><span>Books</span>
                    </a>
                    <a href="{{ route('admin.members.index') }}" aria-label="Members" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.members.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3.5 19v-1.4A4.6 4.6 0 0 1 8.1 13h1.8a4.6 4.6 0 0 1 4.6 4.6V19ZM16 5.5a3 3 0 0 1 0 5.8M17 13.2a4.4 4.4 0 0 1 3.5 4.3V19h-3"/></svg><span>Members</span>
                    </a>
                    <a href="{{ route('admin.loans.index') }}" aria-label="Loans and returns" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.loans.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5h13.5A2.5 2.5 0 0 1 20 8v11H6a2 2 0 0 1-2-2V5.5Z"/><path d="M4 16.5h16M8 9h8M8 12h5"/></svg><span>Loans &amp; returns</span>
                    </a>
                    <a href="{{ route('admin.catalog-data.index') }}" aria-label="Authors and categories" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.catalog-data.*', 'admin.categories.*', 'admin.authors.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/><circle cx="8" cy="5" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="10" cy="19" r="1.5"/></svg><span>Authors &amp; categories</span>
                    </a>
                    <a href="{{ route('admin.fines.index') }}" aria-label="Fines and payments" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.fines.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="14" rx="2"/><path d="M7 9h10M7 13h4M16 15.5h.01"/></svg><span>Fines &amp; payments</span>
                    </a>
                    <a href="{{ route('catalog') }}" class="admin-nav-link" aria-label="Public catalog" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg><span>Public catalog</span>
                    </a>
                </nav>

                <div class="admin-sidebar-bottom">
                    <div class="admin-user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="admin-user-copy"><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></div>
                    <form action="{{ route('logout') }}" method="POST" class="admin-signout-form">@csrf<button type="submit" aria-label="Sign out" title="Sign out"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"/></svg></button></form>
                </div>
            </aside>

            <button class="admin-sidebar-backdrop" type="button" aria-label="Close navigation" data-admin-backdrop></button>

            <div class="admin-main flex-grow-1">
                <header class="admin-topbar navbar">
                    <div class="admin-topbar-left">
                        <button class="admin-sidebar-toggle" type="button" aria-label="Collapse navigation" aria-expanded="true" data-admin-toggle><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 5-7 7 7 7M20 5l-7 7 7 7"/></svg></button>
                        <span class="admin-topbar-divider" aria-hidden="true"></span>
                        <span class="admin-current-page">@yield('title', 'Dashboard')</span>
                    </div>
                    <div class="admin-topbar-right">
                        <button class="admin-topbar-action" type="button" aria-label="Search" title="Search" data-tooltip="Search" aria-haspopup="dialog" aria-controls="admin-search-dialog" aria-expanded="false" data-admin-search-toggle><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg></button>
                        <div class="admin-topbar-tool" data-admin-topbar-tool>
                            <button class="admin-topbar-action" type="button" aria-label="Notifications" title="Notifications" data-tooltip="Notifications" aria-haspopup="true" aria-controls="admin-notifications-panel" aria-expanded="false" data-admin-panel-toggle><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></button>
                            <section class="admin-action-popover admin-notifications-popover" id="admin-notifications-panel" aria-label="Notifications" hidden>
                                <div class="admin-popover-heading"><div><strong>Notifications</strong><small>Library updates</small></div><span class="admin-popover-count">0 new</span></div>
                                <div class="admin-notification-empty"><span class="admin-notification-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></span><strong>You're all caught up</strong><p>New loan and library updates will appear here.</p></div>
                                <a class="admin-popover-footer" href="{{ route('admin.loans.index') }}">View loans and returns <span aria-hidden="true">&rarr;</span></a>
                            </section>
                        </div>
                        <div class="admin-topbar-tool" data-admin-topbar-tool>
                            <button class="admin-topbar-action" type="button" aria-label="Settings" title="Settings" data-tooltip="Settings" aria-haspopup="true" aria-controls="admin-settings-panel" aria-expanded="false" data-admin-panel-toggle><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 1 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 1 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V4a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 1 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2.9Z"/></svg></button>
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
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg>
                            <label class="visually-hidden" id="admin-search-title" for="admin-global-search">Search library books</label>
                            <input id="admin-global-search" type="search" name="q" placeholder="Search books, authors, or ISBNs…" autocomplete="off" required minlength="2" maxlength="100" data-admin-search-input>
                            <button class="admin-search-close" type="button" aria-label="Close search" data-admin-search-close>&times;</button>
                        </form>
                        <div class="admin-search-empty"><span class="admin-search-empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg></span><strong>Search the library catalog</strong><p>Find a book by title, author, or ISBN.</p></div>
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
            <button class="nav-toggle btn btn-outline-success navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-navigation" aria-expanded="false" aria-controls="main-navigation">Menu</button>
            <nav class="main-nav collapse navbar-collapse" id="main-navigation">
                <a href="{{ route('catalog') }}" @class(['is-current' => request()->routeIs('catalog*')])>Explore books</a>
                @auth
                    @if (! auth()->user()->isAdmin())
                        <a href="{{ route('member.account') }}" @class(['is-current' => request()->routeIs('member.*')])>My account</a>
                    @endif
                    <span class="nav-user">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="nav-logout">@csrf<button class="btn button button-quiet button-small" type="submit">Sign out</button></form>
                @else
                    <a class="btn btn-success button button-dark button-small" href="{{ route('login') }}">Member sign in</a>
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
