-- ALTER script to add isSpecial field to categories table
-- Run this script on existing database to add the new field

-- Add isSpecial column to categories table
ALTER TABLE categories ADD COLUMN isSpecial BOOLEAN DEFAULT FALSE;

-- Update existing categories to mark special ones
-- Based on the current category list, mark these as special:
UPDATE categories SET isSpecial = TRUE WHERE name IN (
    'সম্পাদকীয়',
    'লেটার টু এডিটর', 
    'বিশেষ প্রতিবেদন'
);

-- Update category names to match current list (if needed)
-- Note: Only run these if your existing categories have different names

-- Update 'জাতীয়' to 'সারাদেশ' if it exists
UPDATE categories SET name = 'সারাদেশ' WHERE name = 'জাতীয়';

-- Update 'প্রযুক্তি' to 'বিজ্ঞান ও প্রযুক্তি' if it exists  
UPDATE categories SET name = 'বিজ্ঞান ও প্রযুক্তি', slug = 'science-technology' WHERE name = 'প্রযুক্তি';

-- Add missing categories if they don't exist
INSERT IGNORE INTO categories (name, slug, isSpecial) VALUES
    ('ইসলাম', 'islam', FALSE),
    ('লাইফস্টাইল', 'lifestyle', FALSE),
    ('ক্যাম্পাস', 'campus', FALSE),
    ('প্রবাস', 'emigration', FALSE);

-- Remove old categories that are no longer needed (optional)
-- Uncomment these lines if you want to remove old categories:
-- DELETE FROM categories WHERE name IN ('শিশু', 'ধর্ম', 'বিজ্ঞান');

-- Verify the changes
SELECT id, name, slug, isSpecial FROM categories ORDER BY id;
