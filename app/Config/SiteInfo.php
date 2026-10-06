<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Who publishes the site — shown in the footer, About, Contact, Ads, Privacy and
 * Terms pages and in the NewsMediaOrganization JSON-LD. Google's publisher
 * policies (AdSense review) expect a named editor/publisher, a physical address,
 * a phone number and an official email, so fill these in production `.env`:
 *
 *   siteInfo.editorName    = 'নাম'
 *   siteInfo.publisherName = 'নাম'          (omit if the editor is also the publisher)
 *   siteInfo.phone         = '+8801XXXXXXXXX'
 *   siteInfo.whatsapp      = '8801XXXXXXXXX' (digits only, for wa.me links)
 *   siteInfo.registration  = 'তথ্য মন্ত্রণালয় নিবন্ধন নং ...'
 *   siteInfo.facebook / siteInfo.youtube / siteInfo.x / siteInfo.instagram = full URL
 *
 * An empty value is simply not rendered — never ship a placeholder like
 * "+880-XXX-XXXXXXX" or a social icon pointing at an account that doesn't exist.
 */
class SiteInfo extends BaseConfig
{
    public string $siteName = 'বারিন্দ পোস্ট';

    public string $editorName    = 'কাওসার আহমেদ সাগর';
    public string $publisherName = '';

    public string $address = 'মহিশালবাড়ী, গোদাগাড়ী, রাজশাহী, বাংলাদেশ';
    public string $phone   = '+8809658768158';
    public string $whatsapp = '';

    public string $email     = 'editor@barindpost.com';
    public string $newsEmail = 'news@barindpost.com';

    /** Press / ministry registration line, e.g. 'নিবন্ধন নং: ...'. */
    public string $registration = '';

    public string $facebook  = 'https://facebook.com/barindpost';
    public string $youtube   = 'https://www.youtube.com/@BarindPost';
    public string $x         = 'https://x.com/BarindPost';
    public string $instagram = 'https://instagram.com/barindpost';

    /** Shown on /privacy and /terms. Change it whenever either page's text changes. */
    public string $policyUpdated = '৭ অক্টোবর ২০২৬';

    /**
     * A category appears in the header/footer menus only once it has at least
     * this many published articles; thinner sections stay reachable by URL but
     * are noindexed, so neither readers nor reviewers land on an empty page.
     */
    public int $navMinArticles = 5;

    /**
     * Social profiles that are actually configured, in display order:
     * [['url' => ..., 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook'], ...]
     */
    public function socialLinks(): array
    {
        $links = [
            ['url' => $this->facebook,  'icon' => 'fab fa-facebook-f',       'label' => 'Facebook'],
            ['url' => $this->youtube,   'icon' => 'fab fa-youtube',          'label' => 'YouTube'],
            ['url' => $this->x,         'icon' => 'fa-brands fa-x-twitter',  'label' => 'X'],
            ['url' => $this->instagram, 'icon' => 'fab fa-instagram',        'label' => 'Instagram'],
        ];

        return array_values(array_filter($links, static fn ($l) => $l['url'] !== ''));
    }
}
