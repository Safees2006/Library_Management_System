@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<section class="auth-wrap">
    <div class="auth-intro">
        <span class="eyebrow">Your next chapter starts here</span>
        <h1>Welcome back<br><em>to the stacks.</em></h1>
        <p>Sign in to see your loans and due dates, or continue managing the library.</p>
        <a class="text-link" href="{{ route('catalog') }}">← Browse the public catalog</a>
    </div>
    <form class="panel card auth-panel" method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="panel-heading"><span class="eyebrow">Secure access</span><h2>Sign in</h2><p>Use the email address linked to your account.</p></div>
        <label class="field"><span>Email address</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="form-control"></label>
        <label class="field"><span>Password</span><input type="password" name="password" autocomplete="current-password" required class="form-control"></label>
        <label class="check-field"><input type="checkbox" name="remember" value="1" class="form-check-input"><span>Keep me signed in</span></label>
        <button class="btn btn-success button button-dark button-full" type="submit">Sign in to your account <span>→</span></button>
        <p class="form-note">Need access? Ask the library administrator to create your member account.</p>
    </form>
</section>
@endsection
