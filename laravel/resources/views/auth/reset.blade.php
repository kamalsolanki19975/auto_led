@extends('auth.shell')
@section('body')
<h2 class="text-3xl font-bold">Set new password</h2>
<p class="text-slate-400 text-sm mt-1 mb-6">Choose a strong new password.</p>
@if($errors->any())<div class="mb-4 rounded bg-rose-500/10 border border-rose-500/30 px-4 py-3 text-sm text-rose-300">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.update') }}">@csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <input name="email" type="email" class="inp" value="{{ $email }}" required>
  <input name="password" type="password" class="inp" placeholder="New password" required>
  <input name="password_confirmation" type="password" class="inp" placeholder="Confirm password" required>
  <button class="w-full bg-amber-500 text-[#0B0F17] font-semibold py-2.5 rounded">Reset password</button>
</form>
@endsection
