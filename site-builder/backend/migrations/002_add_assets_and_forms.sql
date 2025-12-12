-- ===========================================
-- Migration: Ajout des tables assets et form_submissions
-- ===========================================

-- Table assets (Media Manager)
CREATE TABLE IF NOT EXISTS assets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    original_name VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size INTEGER NOT NULL DEFAULT 0,
    width INTEGER,
    height INTEGER,
    alt_text VARCHAR(255) DEFAULT '',
    tags TEXT[] DEFAULT '{}',
    folder VARCHAR(100) DEFAULT '',
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Index pour assets
CREATE INDEX IF NOT EXISTS idx_assets_project_id ON assets(project_id);
CREATE INDEX IF NOT EXISTS idx_assets_mime_type ON assets(mime_type);
CREATE INDEX IF NOT EXISTS idx_assets_folder ON assets(folder);
CREATE INDEX IF NOT EXISTS idx_assets_tags ON assets USING GIN(tags);

-- Table form_submissions
CREATE TABLE IF NOT EXISTS form_submissions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    page_id UUID REFERENCES pages(id) ON DELETE SET NULL,
    form_id VARCHAR(100),
    form_name VARCHAR(255) DEFAULT 'Contact Form',
    data JSONB NOT NULL DEFAULT '{}',
    visitor_info JSONB DEFAULT '{}',
    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied', 'archived', 'spam')),
    notification_sent BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Index pour form_submissions
CREATE INDEX IF NOT EXISTS idx_form_submissions_project_id ON form_submissions(project_id);
CREATE INDEX IF NOT EXISTS idx_form_submissions_page_id ON form_submissions(page_id);
CREATE INDEX IF NOT EXISTS idx_form_submissions_form_id ON form_submissions(form_id);
CREATE INDEX IF NOT EXISTS idx_form_submissions_status ON form_submissions(status);
CREATE INDEX IF NOT EXISTS idx_form_submissions_created_at ON form_submissions(created_at);

-- Ajouter des colonnes à projects pour les settings de notification
ALTER TABLE projects 
ADD COLUMN IF NOT EXISTS notification_email VARCHAR(255),
ADD COLUMN IF NOT EXISTS webhook_url VARCHAR(500);

-- Fonction pour mettre à jour updated_at automatiquement
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers pour updated_at
DROP TRIGGER IF EXISTS update_assets_updated_at ON assets;
CREATE TRIGGER update_assets_updated_at
    BEFORE UPDATE ON assets
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_form_submissions_updated_at ON form_submissions;
CREATE TRIGGER update_form_submissions_updated_at
    BEFORE UPDATE ON form_submissions
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Permissions
GRANT ALL PRIVILEGES ON TABLE assets TO site_builder_user;
GRANT ALL PRIVILEGES ON TABLE form_submissions TO site_builder_user;
