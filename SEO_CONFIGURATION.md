# SEO & Google Ads Configuration Guide

## Environment Variables Setup

Add these environment variables to your `.env` file:

```env
# Google Analytics 4
GA4_MEASUREMENT_ID=G-XXXXXXXXXX

# Google Search Console
GOOGLE_SITE_VERIFICATION=your-verification-code-here

# Google AdSense
GOOGLE_ADSENSE_ID=ca-pub-XXXXXXXXXX

# Site Configuration
SITE_URL=https://barindpost.com
SITE_NAME=বারিন্দ পোস্ট
SITE_DESCRIPTION=রাজশাহী অঞ্চলের একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল
```

## Setup Instructions

### 1. Google Analytics 4 (GA4)
1. Go to [Google Analytics](https://analytics.google.com/)
2. Create a new GA4 property
3. Get your Measurement ID (format: G-XXXXXXXXXX)
4. Add to `.env` file as `GA4_MEASUREMENT_ID`

### 2. Google Search Console
1. Go to [Google Search Console](https://search.google.com/search-console/)
2. Add your property
3. Choose "HTML tag" verification method
4. Copy the verification code
5. Add to `.env` file as `GOOGLE_SITE_VERIFICATION`

### 3. Google AdSense
1. Go to [Google AdSense](https://www.google.com/adsense/)
2. Create an account and get approved
3. Get your Publisher ID (format: ca-pub-XXXXXXXXXX)
4. Add to `.env` file as `GOOGLE_ADSENSE_ID`

## SEO Features Implemented

### ✅ Completed
- [x] Clean URLs (removed /index.php/)
- [x] Google Analytics 4 tracking
- [x] Google Search Console verification
- [x] Google AdSense integration
- [x] Comprehensive robots.txt
- [x] Lazy loading for images
- [x] Open Graph meta tags
- [x] Twitter Card meta tags
- [x] Structured data (JSON-LD)
- [x] Mobile responsive design
- [x] Bengali language support
- [x] Sitemap functionality

### 🔄 Recommended Next Steps
- [ ] Add breadcrumb structured data
- [ ] Implement article structured data for news pages
- [ ] Add AMP (Accelerated Mobile Pages) support
- [ ] Implement Google Tag Manager
- [ ] Add Core Web Vitals monitoring
- [ ] Create XML sitemap with lastmod dates
- [ ] Add hreflang tags for Bengali content
- [ ] Implement schema markup for news articles

## Ad Placement Recommendations

### Banner Ads
- Top of page (below header)
- Bottom of page (above footer)
- Between news articles

### Inline Ads
- Between news items in lists
- Within article content (after 2-3 paragraphs)

### Sidebar Ads
- Right sidebar on desktop
- Sticky ads for better visibility

## Performance Optimization

### Core Web Vitals
- Lazy loading implemented for images
- Optimized CSS and JavaScript loading
- Compressed assets
- CDN for external resources

### Mobile Optimization
- Responsive design
- Touch-friendly navigation
- Fast loading on mobile networks

## Monitoring & Analytics

### Key Metrics to Track
- Page views and unique visitors
- Bounce rate and session duration
- Traffic sources (organic, direct, social)
- Popular content and categories
- Mobile vs desktop usage
- Bengali vs English content performance

### SEO Monitoring
- Google Search Console for search performance
- Core Web Vitals scores
- Mobile usability issues
- Index coverage and errors

## Content Strategy for SEO

### Bengali Content Optimization
- Use proper Bengali keywords
- Optimize for local search terms
- Include location-based keywords (রাজশাহী, গোদাগাড়ী)
- Create category-specific landing pages

### News SEO Best Practices
- Fresh content updates
- Proper heading structure (H1, H2, H3)
- Image alt text in Bengali
- Internal linking between related articles
- Social sharing optimization
