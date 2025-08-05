# Image Upload and Selection Features

## Overview

This document describes the enhanced image upload and selection functionality implemented in the news management system. The new features allow users to upload images with captions and alt text, and select from previously uploaded images.

## Features Implemented

### 1. Enhanced Image Upload
- **AJAX Upload**: Images are uploaded via AJAX without page refresh
- **Caption Support**: Users can add captions to uploaded images
- **Alt Text Support**: Users can add alt text for accessibility
- **File Validation**: Supports JPG, PNG, GIF, and WebP formats up to 5MB
- **Database Storage**: Images are stored in the `images` table with metadata

### 2. Existing Image Selection
- **Image Browser**: Modal interface to browse all uploaded images
- **Usage Tracking**: Shows how many times each image has been used
- **Preview**: Thumbnail previews of all images
- **Metadata Display**: Shows caption, alt text, and upload date
- **One-Click Selection**: Click to select an image and populate form fields

### 3. Form Integration
- **Unified Interface**: Single form section handles upload, selection, and external URLs
- **Auto-Population**: Selected images automatically populate caption and alt text fields
- **Preview**: Shows selected image with metadata
- **Validation**: Ensures proper data is saved to the database

## Technical Implementation

### Database Schema
The `images` table includes:
- `id`: Primary key
- `image_name`: Display name (caption or filename)
- `image_path`: File path relative to project root
- `original_filename`: Original uploaded filename
- `file_size`: File size in bytes
- `mime_type`: MIME type
- `width` and `height`: Image dimensions
- `caption`: Image caption
- `alt_text`: Alt text for accessibility
- `uploaded_by`: User ID who uploaded the image
- `created_at` and `updated_at`: Timestamps

### Controller Methods

#### ImageUpload Controller
- `upload()`: Handles AJAX image upload with caption and alt text
- `getExistingImages()`: Returns all images with usage statistics

#### Admin Controller
- Updated `newsStore()` and `newsUpdate()` to handle image data from form
- Removed old file upload logic in favor of AJAX upload

### Frontend Components

#### News Form (`app/Views/admin/news_form.php`)
- **Upload Section**: File input, caption, alt text, and upload button
- **Selection Section**: Button to open existing images modal
- **External URL Section**: Input for external image URLs
- **Hidden Fields**: Store image caption and alt text for form submission

#### Modal Interface
- **Bootstrap Modal**: Large modal for image selection
- **Grid Layout**: Responsive grid showing image thumbnails
- **Loading States**: Spinner while loading images
- **Empty State**: Message when no images are available

#### JavaScript Functions
- `loadExistingImages()`: Fetches and displays existing images
- `createImageCard()`: Creates HTML for image selection cards
- `selectExistingImage()`: Handles image selection and form population
- `showImagePreview()`: Displays selected image preview
- `showAlert()`: Shows success/error messages

### Routes
- `POST /image-upload/upload`: Upload new image
- `GET /image-upload/existing-images`: Get all existing images

## Usage Instructions

### For Content Creators

1. **Upload New Image**:
   - Select an image file (JPG, PNG, GIF, WebP, max 5MB)
   - Enter a caption (optional but recommended)
   - Enter alt text for accessibility
   - Click "Upload Image"
   - The image will be saved and form fields will be populated

2. **Select Existing Image**:
   - Click "Browse Existing Images"
   - Browse through uploaded images in the modal
   - Click "Select" on the desired image
   - Form fields will be automatically populated

3. **Use External URL**:
   - Enter an external image URL in the URL field
   - Add caption and alt text manually

### For Administrators

- All uploaded images are stored in the `images` table
- Images can be reused across multiple news articles
- Usage statistics are tracked for each image
- Images are organized by upload date

## File Structure

```
app/
├── Controllers/
│   ├── ImageUpload.php          # Image upload and selection logic
│   └── Admin.php                # Updated news management
├── Models/
│   └── ImageModel.php           # Image database operations
├── Views/admin/
│   └── news_form.php            # Enhanced news form with image features
└── Config/
    └── Routes.php               # Updated routes for image functionality
```

## Benefits

1. **Improved User Experience**: No page refreshes during upload
2. **Better Accessibility**: Alt text support for screen readers
3. **Content Reusability**: Images can be used multiple times
4. **Metadata Management**: Captions and alt text are properly stored
5. **Performance**: AJAX uploads are faster and more responsive
6. **Organization**: Images are properly categorized and searchable

## Future Enhancements

1. **Image Categories**: Organize images by categories
2. **Search Functionality**: Search images by caption or filename
3. **Bulk Operations**: Upload multiple images at once
4. **Image Editing**: Basic image editing (crop, resize, etc.)
5. **CDN Integration**: Store images on CDN for better performance
6. **Image Optimization**: Automatic image compression and optimization

## Testing

Run the test file to verify all components are working:
```bash
docker-compose exec app php test_image_functionality.php
```

This will check:
- Controller existence
- Model existence
- Route configuration
- Form components
- JavaScript functions 