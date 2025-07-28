-- Add featured column to news table if it doesn't exist
ALTER TABLE news ADD COLUMN IF NOT EXISTS featured BOOLEAN NOT NULL DEFAULT FALSE AFTER status; 