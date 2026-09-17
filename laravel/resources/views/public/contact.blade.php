@extends('public.layout')
@section('meta_title','Contact — '.\App\Support\Brand::name())
@section('meta_description','Talk to our team about advertising on the auto network or joining as an auto owner. We respond within 1 business day.')
@section('content')
@php($contact = \App\Support\Brand::contact())
<section class="relative border-b border-white/10">
  <div class="absolute inset-0 grid-bg opacity-50"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 grid lg:grid-cols-2 gap-12">
    <div class="reveal">
      <div class="eyebrow mb-3">Contact</div>
      <h1 class="font-heading text-4xl sm:text-5xl font-extrabold uppercase tracking-tight leading-none">Let's talk</h1>
      <p class="mt-5 text-slate-300 max-w-md">Whether you want to advertise or put a screen on your auto, our team will get back to you within one business day.</p>
      <div class="mt-8 space-y-4 text-sm">
        @if(!empty($contact['email']))<div class="flex items-center gap-3"><span class="h-9 w-9 rounded-lg bg-white/5 flex items-center justify-center"><i data-lucide="mail" class="w-4 h-4 text-brand"></i></span><a href="mailto:{{ $contact['email'] }}" class="text-slate-200 hover:text-white">{{ $contact['email'] }}</a></div>@endif
        @if(!empty($contact['phone']))<div class="flex items-center gap-3"><span class="h-9 w-9 rounded-lg bg-white/5 flex items-center justify-center"><i data-lucide="phone" class="w-4 h-4 text-brand"></i></span><span class="text-slate-200">{{ $contact['phone'] }}</span></div>@endif
        @if(!empty($contact['address']))<div class="flex items-center gap-3"><span class="h-9 w-9 rounded-lg bg-white/5 flex items-center justify-center"><i data-lucide="map-pin" class="w-4 h-4 text-brand"></i></span><span class="text-slate-200">{{ $contact['address'] }}</span></div>@endif
      </div>
    </div>

    <div class="card p-6 md:p-8 reveal">
      @if($errors->any())
        <div class="mb-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-300" data-testid="contact-errors">
          <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif
      <form method="POST" action="{{ route('site.contact.submit') }}" class="space-y-4" data-testid="contact-form">
        @csrf
        <input type="hidden" name="page" value="contact">
        <div>
          <label class="text-xs uppercase tracking-widest text-slate-400 mono">I am a</label>
          <div class="mt-2 grid grid-cols-3 gap-2" x-data="{ t: '{{ $type }}' }">
            @foreach([['advertiser','Advertiser','📢'],['auto_owner','Auto Owner','🚗'],['general','Just Curious','💬']] as [$val,$lbl,$emo])
              <label class="cursor-pointer">
                <input type="radio" name="type" value="{{ $val }}" x-model="t" class="peer sr-only" @checked($type===$val)>
                <span class="block text-center text-sm px-2 py-2 rounded-lg border border-white/10 peer-checked:border-brand peer-checked:bg-brand/10 peer-checked:text-white text-slate-300" data-testid="contact-type-{{ $val }}">{{ $emo }} {{ $lbl }}</span>
              </label>
            @endforeach
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="text-xs uppercase tracking-widest text-slate-400 mono">Name *</label><input name="name" value="{{ old('name') }}" required class="inp mt-1" data-testid="contact-name"></div>
          <div><label class="text-xs uppercase tracking-widest text-slate-400 mono">Company</label><input name="company" value="{{ old('company') }}" class="inp mt-1" data-testid="contact-company"></div>
          <div><label class="text-xs uppercase tracking-widest text-slate-400 mono">Email *</label><input type="email" name="email" value="{{ old('email') }}" required class="inp mt-1" data-testid="contact-email"></div>
          <div><label class="text-xs uppercase tracking-widest text-slate-400 mono">Phone</label><input name="phone" value="{{ old('phone') }}" class="inp mt-1" data-testid="contact-phone"></div>
        </div>
        <div><label class="text-xs uppercase tracking-widest text-slate-400 mono">Message</label><textarea name="message" rows="4" class="inp mt-1" data-testid="contact-message" placeholder="Tell us about your goals…">{{ old('message') }}</textarea></div>
        <button class="btn btn-primary w-full justify-center" data-testid="contact-submit-btn">Send Enquiry <i data-lucide="send" class="w-4 h-4"></i></button>
        <p class="text-xs text-slate-500 text-center">By submitting you agree to our <a href="{{ route('site.legal','privacy') }}" class="text-brand hover:underline">Privacy Policy</a>.</p>
      </form>
    </div>
  </div>
</section>
@php($sym='')
<style>.inp{width:100%;background:#0B0F17;border:1px solid rgba(255,255,255,.1);border-radius:.6rem;padding:.6rem .8rem;font-size:.9rem;color:#F9FAFB}.inp:focus{outline:none;border-color:#F59E0B;box-shadow:0 0 0 1px #F59E0B}</style>
@endsection
