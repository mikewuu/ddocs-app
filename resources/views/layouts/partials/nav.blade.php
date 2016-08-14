<nav class="navbar navbar-default">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">
                <img alt="Brand" src="/images/logo/fc_logo_v1.svg" class="img-logo">
            </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                @if(Auth::check())
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle text-capitalize" data-toggle="dropdown" role="button"
                           aria-haspopup="true"
                           aria-expanded="false">{{ Auth::user()->name }} @include('layouts.partials.credit-or-subscribed')</a>
                        <ul class="dropdown-menu">
                            <li><a href="/checklist/make">New Checklist</a></li>
                            <li><a href="/checklist">My Lists</a></li>
                            <li><a href="/account">Account</a></li>
                            <li><a href="/logout" alt="link to logout">Logout</a></li>
                        </ul>
                    </li>
                @else
                    <li>
                        <a href="/login" class="navbar-link">
                            Login
                        </a>
                    </li>
                @endif
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>