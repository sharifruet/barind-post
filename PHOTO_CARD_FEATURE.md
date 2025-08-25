# Photo Card Generation Feature

## Overview
The Photo Card Generation feature allows admin users to create social media-ready photo cards from news articles. This feature is designed to help create engaging visual content for social media platforms.

## Features

### Access Control
- **Admin Only**: This feature is restricted to users with the 'admin' role
- **Secure Routes**: All photo card generation routes are protected with role-based access control

### Functionality
1. **News Selection**: Choose from published news articles
2. **Template Selection**: Two design templates available:
   - Default (White Background)
   - Header & Footer Layout
3. **Preview**: Real-time preview of selected news article
4. **Generation**: AJAX-based photo card generation
5. **Download**: Direct download of generated photo cards
6. **URL Copy**: Copy image URL for sharing

### Technical Details

#### Image Specifications
- **Dimensions**: 1200x630 pixels (optimal for social media)
- **Format**: PNG with transparency support
- **Quality**: High-resolution output

#### Components
- **Logo Integration**: Automatically includes site logo if available
- **News Image**: Incorporates news article image if present
- **Text Overlay**: News title, publication date, and website URL
- **Font Support**: Bengali and English text support with fallback fonts

#### File Structure
```
app/Controllers/Admin.php
├── photoCardGenerator() - Main view method
├── generatePhotoCard() - AJAX generation endpoint
├── createPhotoCard() - Image creation logic
└── wrapText() - Text wrapping utility

app/Views/admin/photo_card_generator.php - User interface

public/uploads/photo_cards/ - Generated image storage
```

#### Routes
- `GET /admin/photo-card-generator` - Main interface
- `POST /admin/photo-card-generator/generate` - AJAX generation endpoint

## Usage

### For Admin Users
1. Navigate to Admin Dashboard
2. Click "Photo Card Generator" in the sidebar
3. Select a news article from the dropdown
4. Choose a template design
5. Click "Generate Photo Card"
6. Download or copy the URL of the generated image

### Templates
- **Default**: Clean white background with black text, image overlay with title at bottom
- **Header & Footer Layout**: Structured layout with image (full height) → title section (minimum 200px) → footer (minimum 80px), image uses natural dimensions, logo positioned in title section, red title background with white text, and black footer with "বরিন্দ পোস্ট | [Category]" and date

## Security Features
- Role-based access control (admin only)
- Input validation and sanitization
- Secure file handling
- Directory traversal protection
- CSRF protection via CodeIgniter's built-in security

## Error Handling
- Font fallback system for different operating systems
- Graceful handling of missing images
- Comprehensive error messages
- Loading states and user feedback

## Browser Compatibility
- Modern browsers with ES6+ support
- AJAX functionality
- File download capabilities
- Clipboard API support for URL copying

## Dependencies
- PHP GD extension for image manipulation
- CodeIgniter 4 framework
- Bootstrap 5 for UI components
- Font Awesome for icons

## Future Enhancements
- Additional template designs
- Custom color schemes
- Batch generation for multiple articles
- Social media platform-specific formats
- Custom text positioning options 