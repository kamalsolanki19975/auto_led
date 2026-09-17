<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Lead;
use App\Models\PricingPlan;
use App\Models\SystemSetting;
use App\Services\EmailService;
use App\Services\NotificationService;
use App\Services\PublicStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    /** Curated marketing imagery (do not hardcode business data — imagery only). */
    protected function img(): array
    {
        return [
            'hero_auto' => 'https://images.unsplash.com/photo-1771694584166-c66969e62355?crop=entropy&cs=srgb&fm=jpg&q=85&w=1600',
            'auto_motion' => 'https://images.unsplash.com/photo-1626149637281-4e227308da18?crop=entropy&cs=srgb&fm=jpg&q=85&w=1200',
            'auto_night' => 'https://images.pexels.com/photos/11832470/pexels-photo-11832470.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=1200',
            'billboard' => 'https://images.unsplash.com/photo-1616418625172-c607e16733ca?crop=entropy&cs=srgb&fm=jpg&q=85&w=1200',
            'neon_night' => 'https://images.pexels.com/photos/36837687/pexels-photo-36837687.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=1200',
            'city_traffic' => 'https://images.pexels.com/photos/14780175/pexels-photo-14780175.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=1200',
            'analytics' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?crop=entropy&cs=srgb&fm=jpg&q=85&w=1200',
            'tech_screen' => 'https://images.unsplash.com/photo-1576608583800-2dea5261c7ee?crop=entropy&cs=srgb&fm=jpg&q=85&w=1200',
        ];
    }

    public function home(PublicStatsService $stats)
    {
        return view('public.home', [
            'stats' => $stats->summary(),
            'img' => $this->img(),
            'plans' => PricingPlan::active()->orderBy('sort_order')->get(),
            'faqs' => Faq::active()->orderBy('sort_order')->get()->groupBy('category'),
        ]);
    }

    public function howItWorks()
    {
        return view('public.how-it-works', ['img' => $this->img()]);
    }

    public function advertisers(PublicStatsService $stats)
    {
        return view('public.advertisers', ['img' => $this->img(), 'stats' => $stats->summary()]);
    }

    public function autoOwners(PublicStatsService $stats)
    {
        return view('public.auto-owners', ['img' => $this->img(), 'stats' => $stats->summary()]);
    }

    public function network(PublicStatsService $stats)
    {
        return view('public.network', [
            'img' => $this->img(),
            'stats' => $stats->summary(),
            'coverage' => $stats->coverage(),
            'campaigns' => $stats->campaignBreakdown(),
        ]);
    }

    public function solutions()
    {
        return view('public.solutions', ['img' => $this->img()]);
    }

    public function technology()
    {
        return view('public.technology', ['img' => $this->img()]);
    }

    public function analytics(PublicStatsService $stats)
    {
        return view('public.analytics', ['img' => $this->img(), 'stats' => $stats->summary()]);
    }

    public function developers()
    {
        return view('public.developers', ['img' => $this->img()]);
    }

    public function about(PublicStatsService $stats)
    {
        return view('public.about', ['img' => $this->img(), 'stats' => $stats->summary()]);
    }

    public function pricing()
    {
        return view('public.pricing', [
            'img' => $this->img(),
            'plans' => PricingPlan::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function faq()
    {
        return view('public.faq', [
            'faqs' => Faq::active()->orderBy('sort_order')->get()->groupBy('category'),
        ]);
    }

    public function contact()
    {
        return view('public.contact', ['img' => $this->img(), 'type' => request('type', 'general')]);
    }

    public function contactSubmit(Request $request, NotificationService $notif, EmailService $mail)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'company' => 'nullable|string|max:150',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|string|max:40',
            'message' => 'nullable|string|max:3000',
            'type' => 'nullable|in:general,advertiser,auto_owner',
            'page' => 'nullable|string|max:120',
        ]);

        $lead = Lead::create([
            'code' => 'LEAD-'.strtoupper(Str::random(6)),
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'] ?? null,
            'type' => $data['type'] ?? 'general',
            'source' => 'website',
            'page' => $data['page'] ?? 'contact',
            'status' => 'new',
        ]);

        $typeLabel = ['advertiser' => 'Advertiser', 'auto_owner' => 'Auto Owner', 'general' => 'General'][$lead->type] ?? 'General';

        $notif->notifyAdmins('lead.created', [
            'title' => '🔔 New Website Enquiry',
            'message' => $lead->name.($lead->company ? ' ('.$lead->company.')' : '').' · '.$typeLabel.' · '.$lead->email,
            'type' => 'information',
            'category' => 'lead',
            'related_type' => Lead::class,
            'related_id' => $lead->id,
            'link' => route('leads.show', $lead),
        ]);

        try {
            $mail->send('lead.received', $lead->email, [
                'user_name' => $lead->name,
                'message' => 'Thank you for contacting '.\App\Support\Brand::name().'. Our team will get back to you within 1 business day.',
                'title' => 'We received your enquiry',
            ]);
        } catch (\Throwable $e) {
            // mock mailer / template optional — never block the lead
        }

        return back()->with('success', 'Thank you, '.$lead->name.'! Your enquiry has been received. Our team will contact you within 1 business day.');
    }

    public function legal(string $doc)
    {
        $titles = ['privacy' => 'Privacy Policy', 'terms' => 'Terms & Conditions', 'cookie' => 'Cookie Policy'];
        $title = $titles[$doc] ?? 'Legal';
        $body = SystemSetting::get('legal', $doc, $this->defaultLegal($doc));

        return view('public.legal', ['title' => $title, 'body' => $body, 'doc' => $doc]);
    }

    public function stats(PublicStatsService $stats)
    {
        return response()->json($stats->summary());
    }

    public function sitemap(PublicStatsService $stats)
    {
        $routes = ['site.home', 'site.how', 'site.advertisers', 'site.owners', 'site.network',
            'site.solutions', 'site.technology', 'site.analytics', 'site.developers', 'site.about',
            'site.pricing', 'site.faq', 'site.contact'];
        $urls = array_map(fn ($r) => route($r), $routes);
        $urls[] = route('site.legal', 'privacy');
        $urls[] = route('site.legal', 'terms');
        $urls[] = route('site.legal', 'cookie');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= "  <url><loc>{$u}</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $body = "User-agent: *\nAllow: /\nDisallow: /dashboard\nDisallow: /settings\nDisallow: /api/\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200)->header('Content-Type', 'text/plain');
    }

    protected function defaultLegal(string $doc): string
    {
        $company = \App\Support\Brand::name();

        return match ($doc) {
            'privacy' => "<p>{$company} respects your privacy. This policy explains what data we collect through our website and advertising network, how we use it, and your rights. We only collect information you voluntarily provide (such as contact enquiries) and privacy-conscious, aggregated advertising performance data. We never sell personal data.</p><p>For any privacy request, contact us via the details on our Contact page.</p>",
            'terms' => "<p>These Terms govern your use of the {$company} website and platform. By accessing our services you agree to use them lawfully and not to disrupt the advertising network or its devices. Advertising campaigns, settlements and proof-of-play are governed by the commercial agreement signed with {$company}.</p>",
            'cookie' => "<p>{$company} uses a minimal set of strictly-necessary cookies to keep you signed in and to secure forms. We use privacy-conscious, aggregated analytics and do not use invasive third-party advertising trackers on this website.</p>",
            default => '<p>Content coming soon.</p>',
        };
    }
}
