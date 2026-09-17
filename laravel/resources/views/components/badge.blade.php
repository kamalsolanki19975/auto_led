@php
  $map = [
    'active'=>'emerald','online'=>'emerald','valid'=>'emerald','paid'=>'emerald','approved'=>'emerald','completed'=>'emerald','delivered'=>'emerald','settled'=>'emerald','installed'=>'emerald','sent'=>'emerald','received'=>'sky',
    'offline'=>'rose','critical'=>'rose','invalid'=>'rose','overdue'=>'rose','rejected'=>'rose','blocked'=>'rose','faulty'=>'rose','failed'=>'rose','high'=>'rose','decommissioned'=>'rose',
    'pending'=>'amber','pending_approval'=>'amber','under_delivery'=>'amber','under_maintenance'=>'amber','maintenance'=>'amber','warning'=>'amber','suspended'=>'amber','expired'=>'amber','installation_pending'=>'amber','waiting'=>'amber','unpaid'=>'amber','partially_paid'=>'amber','open'=>'amber','review'=>'amber','draft'=>'sky','submitted'=>'sky','under_review'=>'sky','information'=>'sky','scheduled'=>'sky','assigned'=>'sky','in_progress'=>'sky','registered'=>'slate','available'=>'slate','inventory'=>'slate','inactive'=>'slate','paused'=>'slate','cancelled'=>'slate','none'=>'slate','medium'=>'sky','low'=>'slate',
  ];
  $s = strtolower((string)($status ?? ''));
  $c = $map[$s] ?? 'slate';
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium bg-{{ $c }}-500/10 text-{{ $c }}-400 border border-{{ $c }}-500/30">
  <span class="h-1.5 w-1.5 rounded-full bg-{{ $c }}-500"></span>{{ ucwords(str_replace('_',' ',$status ?? '—')) }}
</span>
