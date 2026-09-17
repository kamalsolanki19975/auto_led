<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in · AutoAds Network</title>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>body{font-family:'Plus Jakarta Sans',sans-serif}h1,h2{font-family:'Barlow Condensed',sans-serif}
.inp{width:100%;background:#172033;border:1px solid rgba(255,255,255,.1);border-radius:.4rem;padding:.65rem .75rem;color:#F3F4F6}
.inp:focus{outline:none;border-color:#F59E0B;box-shadow:0 0 0 1px #F59E0B}</style>
</head>
<body class="min-h-screen bg-[#0B0F17] text-slate-100 flex">
  <div class="hidden lg:flex flex-col justify-between w-1/2 p-12 relative overflow-hidden" style="background:#070A10">
    <div class="flex items-center gap-2"><span class="inline-flex h-9 w-9 items-center justify-center rounded bg-amber-500 text-[#0B0F17] font-bold text-lg">A</span><span class="font-heading text-2xl font-bold tracking-wide">AUTOADS<span class="text-amber-500">·</span>NETWORK</span></div>
    <div><h1 class="text-5xl font-extrabold leading-tight">Digital advertising,<br><span class="text-amber-500">on every auto.</span></h1>
      <p class="mt-4 text-slate-400 max-w-md">The complete DOOH operating platform — from campaign to proof-of-play, driver earnings, settlements and profitability.</p></div>
    <p class="text-xs text-slate-600 font-mono">CAMPAIGN → AUTO → SCREEN → DEVICE → PLAYBACK → PROOF OF PLAY → RUNTIME → EARNINGS → SETTLEMENT → FINANCE</p>
  </div>
  <div class="flex-1 flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
      <div class="lg:hidden flex items-center gap-2 mb-8"><span class="inline-flex h-9 w-9 items-center justify-center rounded bg-amber-500 text-[#0B0F17] font-bold">A</span><span class="font-heading text-xl font-bold">AUTOADS·NET</span></div>
      <h2 class="text-3xl font-bold">Sign in</h2>
      <p class="text-slate-400 text-sm mt-1 mb-6">Welcome back. Enter your credentials to continue.</p>
      @if($errors->any())<div class="mb-4 rounded bg-rose-500/10 border border-rose-500/30 px-4 py-3 text-sm text-rose-300" data-testid="login-error">{{ $errors->first() }}</div>@endif
      @if(session('success'))<div class="mb-4 rounded bg-emerald-500/10 border border-emerald-500/30 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>@endif
      <form method="POST" action="{{ route('login.attempt') }}" x-data="{show:false}" x-cloak>
        @csrf
        <label class="text-xs text-slate-400 uppercase tracking-wide">Email</label>
        <input name="email" type="email" value="{{ old('email','admin@autoads.test') }}" class="inp mt-1 mb-4" data-testid="login-email" required autofocus>
        <label class="text-xs text-slate-400 uppercase tracking-wide">Password</label>
        <div class="relative mt-1 mb-4">
          <input :type="show?'text':'password'" name="password" value="Admin@123" class="inp pr-10" data-testid="login-password" required>
          <button type="button" @click="show=!show" class="absolute right-3 top-2.5 text-slate-500 text-xs" x-text="show?'HIDE':'SHOW'"></button>
        </div>
        <div class="flex items-center justify-between mb-6 text-sm">
          <label class="flex items-center gap-2 text-slate-400"><input type="checkbox" name="remember" class="rounded bg-[#172033] border-white/20"> Remember me</label>
          <a href="{{ route('password.request') }}" class="text-amber-500">Forgot password?</a>
        </div>
        <button class="w-full bg-amber-500 hover:bg-amber-600 text-[#0B0F17] font-semibold py-2.5 rounded" data-testid="login-submit">Sign in</button>
      </form>
      <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    </div>
  </div>
</body></html>
