<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Branding Image -->
            <a class="navbar-brand" href="{{ url('/') }}">
                <span><img alt="Brand" src="/logo-sm.png"></span> {{ config('app.name') }}
            </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <!-- Left Side Of Navbar -->
            <ul class="nav navbar-nav">
                {{-- <li><a href="/oncall">On Call</a></li> --}}
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        Dept :: {{ config('app.department') }} <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="https://pto-it.alliedhealthmedia.com">IT</a></li>
                        <li><a href="https://pto-product.alliedhealthmedia.com">Product</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        Team @if(isset($selectedteam) && $selectedteam) :: {{ $selectedteam->name }} @endif <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="/">All</a></li>
                        @foreach(\App\Tag::orderBy('name', 'asc')->get() as $t)
                            <li><a href="{{ $t->link() }}">{{ $t->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
            </ul>

            <form class="navbar-form navbar-left" method="GET" action="/">
                <div class="form-group">
                    {{ Form::text('q', null, ['placeholder' => 'Find Employee', 'class' => 'form-control']) }}
                    {{-- <input type="text" name="q" class="form-control" placeholder="Find Employee"> --}}
                </div>
                <button type="submit" class="btn btn-default">Find</button>
            </form>

            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right">
                <!-- Authentication Links -->
                @if (Auth::guest())
                    @if (isset($user))
                        <li><a href="/" title="Google User">{{ $user->name }}</a></li>
                    @endif
                    <li><a href="{{ url('/login') }}">Login</a></li>
                    <li><a href="{{ url('/register') }}">Register</a></li>
                @else
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu">
                            @if (Auth::user()->isAdmin())
                                <li><a href="/admin">Employees</a></li>
                                <li><a href="/admin/holidays">Holidays</a></li>
                                <li><a href="/admin/teams">Teams</a></li>
                                <li><a href="/admin/users">Users</a></li>
                            @endif
                            <li class="divider"></li>
                            <li>
                                <a href="{{ url('/logout') }}"
                                    onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                    Logout
                                </a>

                                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>