<ul class="nav flex-column p-3">
    <!-- Dashboard -->
    <li class="nav-item mb-2">
        <a class="nav-link fw-bold text-white px-2 py-2 {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           style="background-color: #ff6339;"
           href="{{ route('dashboard') }}">
            <i class="fas fa-film me-2"></i> DASHBOARD
        </a>
    </li>
    <!-- Movie & Web Series Dropdown -->
    <li class="nav-item dropdown mb-2">
        @if(Auth::user()->role === 'admin')
        <a class="nav-link dropdown-toggle text-dark {{ request()->is('content/movies*') || request()->is('content/webseries*') ? 'active' : '' }}"
           href="#" id="movieWebDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-video me-2"></i> Movie & Web Series
        </a>
        @else
        <a class="nav-link dropdown-toggle text-dark {{ request()->is('content/movies*') || request()->is('content/webseries*') ? 'active' : '' }}"
           href="#" id="movieWebDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-video me-2"></i> Movie
        </a>
        @endif

        <ul class="dropdown-menu" aria-labelledby="movieWebDropdown">
            {{-- Movies (for admin & channel) --}}
            @if(Auth::user()->role === 'channel' || Auth::user()->role === 'admin')
                <li><h6 class="dropdown-header text-uppercase">Movies</h6></li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('content.movies.index') ? 'active' : '' }}" href="{{ route('content.movies.index') }}">
                        <i class="fas fa-list me-2"></i> All Movies
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('content.movies.create') ? 'active' : '' }}" href="{{ route('content.movies.create') }}">
                        <i class="fas fa-plus me-2"></i> Add Movie
                    </a>
                </li>
            @endif

            {{-- Admin-only content --}}
            @if(Auth::user()->role === 'admin')
                {{-- Web Series --}}
                <li><h6 class="dropdown-header text-uppercase">Web Series</h6></li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('content.webseries.index') ? 'active' : '' }}" href="{{ route('content.webseries.index') }}">
                        <i class="fas fa-tv me-2"></i> All Web Series
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('content.webseries.create') ? 'active' : '' }}" href="{{ route('content.webseries.create') }}">
                        <i class="fas fa-plus me-2"></i> Add Web Series
                    </a>
                </li>
            @endif
        </ul>

    </li>
    @if(Auth::user()->role !== 'channel' || Auth::user()->role === 'admin')

    <!-- Other Links -->
    <li class="nav-item mb-2">
        <a href="{{ route('plan.index') }}" class="nav-link text-dark {{ request()->routeIs('plan.index') ? 'active' : '' }}">
            <i class="fas fa-list me-2"></i> Package Plan
        </a>
    </li>

    <!-- Channel Management -->
    <li class="nav-item dropdown mb-2">
        <a class="nav-link dropdown-toggle text-dark" href="#" id="channelDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-cogs me-2"></i> Channel Management
        </a>
        <ul class="dropdown-menu" aria-labelledby="channelDropdown">
            <li><a class="dropdown-item" href="{{ route('channels.index') }}"><i class="fas fa-list me-2"></i> Channel Request</a></li>
            <li><a class="dropdown-item" href="{{ route('channels.approve') }}"><i class="fas fa-list me-2"></i> Approve Channel</a></li>
        </ul>
    </li>

    <li class="nav-item mb-2">
        <a href="{{ route('content.genre.index') }}" class="nav-link text-dark {{ request()->routeIs('content.genre.index') ? 'active' : '' }}">
            <i class="fas fa-folder me-2"></i> GENRE
        </a>
    </li>

    <li class="nav-item mb-2">
        <a href="{{ route('banner.index') }}" class="nav-link text-dark {{ request()->routeIs('banner.index') ? 'active' : '' }}">
            <i class="fas fa-scroll me-2"></i> Banner
        </a>
    </li>

    <li class="nav-item mb-2">
        <a href="{{ route('users.index') }}" class="nav-link text-dark {{ request()->routeIs('users.index') ? 'active' : '' }}">
            <i class="fas fa-user me-2"></i> USERS
        </a>
    </li>
    @endif
    @if(Auth::user()->role === 'channel' || Auth::user()->role === 'admin')

    <li class="nav-item mb-2">
        <a href="{{ route('tlogs.index') }}" class="nav-link text-dark {{ request()->routeIs('tlogs.index') ? 'active' : '' }}">
            <i class="fas fa-clock me-2"></i> TRANSACTION LOG
        </a>
    </li>
    @else

    <li class="nav-item mb-2">
        <a href="{{ route('notify.index') }}" class="nav-link text-dark {{ request()->routeIs('notify.index') ? 'active' : '' }}">
            <i class="fas fa-bell me-2"></i> Notification
        </a>
    </li>
    @endif
    <!-- Profile Dropdown -->
    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle text-dark" href="#" role="button"
           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name) }}"
                 class="rounded-circle me-2" width="30" height="30" alt="User Avatar">
            {{ Auth::user()->name }}
            <i class="fas fa-chevron-down ms-1"></i>
        </a>

        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="{{ route('show.profile') }}">
                <i class="fas fa-user me-2"></i>Profile
            </a>
            <a class="dropdown-item" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt me-2"></i>{{ __('Logout') }}
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </li>
</ul>
