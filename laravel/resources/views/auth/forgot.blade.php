@extends('auth.shell')
@section('body')
<h2 class="text-3xl font-bold">Reset password</h2>
<p class="text-slate-400 text-sm mt-1 mb-6">Enter your email and we'll send a reset link.</p>
@if(session('success'))<div class="mb-4 rounded bg-emerald-500/10 border border-emerald-500/30 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>@endif
<form method="POST" action="{{ route('password.email') }}">@csrf
  <input name="email" type="email" class="inp mb-4" placeholder="you@company.com" required>
  <button class="w-full bg-amber-500 text-[#0B0F17] font-semibold py-2.5 rounded">Send reset link</button>
</form>
<a href="{{ route('login') }}" class="block mt-4 text-sm text-amber-500">Back to sign in</a>
@endsection
