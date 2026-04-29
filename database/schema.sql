-- PropIntel CRM Database Schema
-- MySQL 8.0+
-- Created: 2024

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- Drop all tables in reverse dependency order
DROP TABLE IF EXISTS deal_calculations;
DROP TABLE IF EXISTS deal_analyses;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS letter_logs;
DROP TABLE IF EXISTS sms_logs;
DROP TABLE IF EXISTS email_logs;
DROP TABLE IF EXISTS campaign_leads;
DROP TABLE IF EXISTS campaign_steps;
DROP TABLE IF EXISTS campaigns;
DROP TABLE IF EXISTS message_templates;
DROP TABLE IF EXISTS lead_tasks;
DROP TABLE IF EXISTS lead_notes;
DROP TABLE IF EXISTS lead_scores;
DROP TABLE IF EXISTS lead_flags;
DROP TABLE IF EXISTS lead_owner_details;
DROP TABLE IF EXISTS lead_property_details;
DROP TABLE IF EXISTS import_rows;
DROP TABLE IF EXISTS import_jobs;
DROP TABLE IF EXISTS api_providers;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS leads;
DROP TABLE IF EXISTS users;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120)  NOT NULL,
    email       VARCHAR(180)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('admin','acquisitions','marketing','viewer') NOT NULL DEFAULT 'viewer',
    active      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login  DATETIME      NULL,
    INDEX idx_role (role),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LEADS (core record)
-- ============================================================
CREATE TABLE leads (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    -- Address
    address         VARCHAR(255) NOT NULL,
    city            VARCHAR(100) NOT NULL,
    state           VARCHAR(2)   NOT NULL,
    zip             VARCHAR(10)  NOT NULL,
    county          VARCHAR(100) NULL,
    -- Parcel
    apn             VARCHAR(80)  NULL,
    -- Classification
    property_type   ENUM('Single Family','Multi Family','Condo','Townhouse','Mobile Home','Land','Commercial','Other') NOT NULL DEFAULT 'Single Family',
    -- Status & Scoring
    status          ENUM('New','Researching','Skip Trace Needed','Contacted','Follow-Up','Appointment Set','Offer Made','Under Contract','Closed','Dead') NOT NULL DEFAULT 'New',
    lead_score      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    score_reason    TEXT NULL,
    -- Key dates
    last_contact_date DATE NULL,
    follow_up_date    DATE NULL,
    -- Flags (stored here for fast filtering)
    is_absentee_owner TINYINT(1) NOT NULL DEFAULT 0,
    is_vacant         TINYINT(1) NOT NULL DEFAULT 0,
    is_pre_foreclosure TINYINT(1) NOT NULL DEFAULT 0,
    is_tax_delinquent TINYINT(1) NOT NULL DEFAULT 0,
    is_probate        TINYINT(1) NOT NULL DEFAULT 0,
    is_tired_landlord TINYINT(1) NOT NULL DEFAULT 0,
    is_high_equity    TINYINT(1) NOT NULL DEFAULT 0,
    is_mls_listed     TINYINT(1) NOT NULL DEFAULT 0,
    -- Metadata
    created_by      INT UNSIGNED NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL,
    INDEX idx_zip        (zip),
    INDEX idx_city       (city),
    INDEX idx_state      (state),
    INDEX idx_status     (status),
    INDEX idx_score      (lead_score),
    INDEX idx_absentee   (is_absentee_owner),
    INDEX idx_vacant     (is_vacant),
    INDEX idx_preforeclosure (is_pre_foreclosure),
    INDEX idx_tax        (is_tax_delinquent),
    INDEX idx_probate    (is_probate),
    INDEX idx_high_equity (is_high_equity),
    INDEX idx_created_at (created_at),
    INDEX idx_follow_up  (follow_up_date),
    INDEX idx_apn        (apn),
    FULLTEXT idx_ft_address (address, city, county),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LEAD PROPERTY DETAILS
-- ============================================================
CREATE TABLE lead_property_details (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id         INT UNSIGNED NOT NULL UNIQUE,
    beds            DECIMAL(4,1) NULL,
    baths           DECIMAL(4,1) NULL,
    sqft            INT UNSIGNED NULL,
    lot_size        VARCHAR(50)  NULL,   -- e.g. "0.25 acres"
    year_built      SMALLINT UNSIGNED NULL,
    estimated_value DECIMAL(12,2) NULL,
    estimated_rent  DECIMAL(10,2) NULL,
    loan_balance    DECIMAL(12,2) NULL,
    equity_estimate DECIMAL(12,2) NULL,
    last_sale_date  DATE NULL,
    last_sale_price DECIMAL(12,2) NULL,
    arv_estimate    DECIMAL(12,2) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LEAD OWNER DETAILS
-- ============================================================
CREATE TABLE lead_owner_details (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id             INT UNSIGNED NOT NULL UNIQUE,
    owner_name          VARCHAR(255) NULL,
    owner_first_name    VARCHAR(100) NULL,
    owner_last_name     VARCHAR(100) NULL,
    owner_phone         VARCHAR(30)  NULL,
    owner_phone2        VARCHAR(30)  NULL,
    owner_email         VARCHAR(180) NULL,
    mailing_address     VARCHAR(255) NULL,
    mailing_city        VARCHAR(100) NULL,
    mailing_state       VARCHAR(2)   NULL,
    mailing_zip         VARCHAR(10)  NULL,
    owner_occupied      TINYINT(1)   NOT NULL DEFAULT 0,
    out_of_state_owner  TINYINT(1)   NOT NULL DEFAULT 0,
    ownership_years     SMALLINT UNSIGNED NULL,
    do_not_contact      TINYINT(1)   NOT NULL DEFAULT 0,
    skip_traced         TINYINT(1)   NOT NULL DEFAULT 0,
    skip_trace_date     DATE NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LEAD SCORES (history)
-- ============================================================
CREATE TABLE lead_scores (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id     INT UNSIGNED NOT NULL,
    score       TINYINT UNSIGNED NOT NULL,
    reason      TEXT NULL,
    scored_by   INT UNSIGNED NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead (lead_id),
    FOREIGN KEY (lead_id)   REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (scored_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LEAD NOTES
-- ============================================================
CREATE TABLE lead_notes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id     INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NULL,
    note        TEXT NOT NULL,
    note_type   ENUM('general','call','email','sms','visit','system') NOT NULL DEFAULT 'general',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lead (lead_id),
    FOREIGN KEY (lead_id)  REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LEAD TASKS
-- ============================================================
CREATE TABLE lead_tasks (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id     INT UNSIGNED NOT NULL,
    assigned_to INT UNSIGNED NULL,
    created_by  INT UNSIGNED NULL,
    task_type   ENUM('Call','Text','Email','Research','Drive-by','Make Offer','Follow-up','Other') NOT NULL DEFAULT 'Call',
    title       VARCHAR(255) NOT NULL,
    notes       TEXT NULL,
    due_date    DATE NULL,
    status      ENUM('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
    completed_at DATETIME NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lead        (lead_id),
    INDEX idx_assigned    (assigned_to),
    INDEX idx_due_date    (due_date),
    INDEX idx_status      (status),
    FOREIGN KEY (lead_id)     REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CAMPAIGNS
-- ============================================================
CREATE TABLE campaigns (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    description     TEXT NULL,
    type            ENUM('direct_mail','email','sms','cold_call','mixed') NOT NULL DEFAULT 'mixed',
    status          ENUM('draft','active','paused','completed','archived') NOT NULL DEFAULT 'draft',
    created_by      INT UNSIGNED NULL,
    start_date      DATE NULL,
    end_date        DATE NULL,
    total_leads     INT UNSIGNED NOT NULL DEFAULT 0,
    emails_sent     INT UNSIGNED NOT NULL DEFAULT 0,
    sms_sent        INT UNSIGNED NOT NULL DEFAULT 0,
    letters_sent    INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status     (status),
    INDEX idx_created_by (created_by),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CAMPAIGN STEPS (sequence steps)
-- ============================================================
CREATE TABLE campaign_steps (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id     INT UNSIGNED NOT NULL,
    step_number     TINYINT UNSIGNED NOT NULL DEFAULT 1,
    step_type       ENUM('email','sms','letter','call','wait') NOT NULL,
    delay_days      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    template_id     INT UNSIGNED NULL,
    subject         VARCHAR(255) NULL,
    body            TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign (campaign_id),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CAMPAIGN LEADS (assignment)
-- ============================================================
CREATE TABLE campaign_leads (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id     INT UNSIGNED NOT NULL,
    lead_id         INT UNSIGNED NOT NULL,
    current_step    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status          ENUM('active','paused','completed','unsubscribed','bounced') NOT NULL DEFAULT 'active',
    added_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_campaign_lead (campaign_id, lead_id),
    INDEX idx_campaign (campaign_id),
    INDEX idx_lead     (lead_id),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id)     REFERENCES leads(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MESSAGE TEMPLATES
-- ============================================================
CREATE TABLE message_templates (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    type        ENUM('email','sms','letter') NOT NULL,
    category    VARCHAR(100) NULL,   -- e.g. 'absentee_owner', 'probate'
    subject     VARCHAR(255) NULL,
    body        LONGTEXT NOT NULL,
    variables   JSON NULL,           -- list of supported {{variables}}
    created_by  INT UNSIGNED NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type     (type),
    INDEX idx_category (category),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- EMAIL LOGS
-- ============================================================
CREATE TABLE email_logs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id         INT UNSIGNED NULL,
    campaign_id     INT UNSIGNED NULL,
    user_id         INT UNSIGNED NULL,
    to_email        VARCHAR(180) NOT NULL,
    from_email      VARCHAR(180) NOT NULL,
    subject         VARCHAR(255) NOT NULL,
    body            LONGTEXT NOT NULL,
    status          ENUM('queued','sent','delivered','opened','clicked','bounced','failed','spam') NOT NULL DEFAULT 'queued',
    sendgrid_id     VARCHAR(255) NULL,
    sent_at         DATETIME NULL,
    opened_at       DATETIME NULL,
    error_message   TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead     (lead_id),
    INDEX idx_campaign (campaign_id),
    INDEX idx_status   (status),
    FOREIGN KEY (lead_id)     REFERENCES leads(id)     ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SMS LOGS
-- ============================================================
CREATE TABLE sms_logs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id         INT UNSIGNED NULL,
    campaign_id     INT UNSIGNED NULL,
    user_id         INT UNSIGNED NULL,
    to_phone        VARCHAR(20) NOT NULL,
    from_phone      VARCHAR(20) NOT NULL,
    body            TEXT NOT NULL,
    direction       ENUM('outbound','inbound') NOT NULL DEFAULT 'outbound',
    status          ENUM('queued','sent','delivered','failed','received') NOT NULL DEFAULT 'queued',
    twilio_sid      VARCHAR(60) NULL,
    sent_at         DATETIME NULL,
    error_message   TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead     (lead_id),
    INDEX idx_campaign (campaign_id),
    INDEX idx_direction (direction),
    FOREIGN KEY (lead_id)     REFERENCES leads(id)     ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LETTER LOGS
-- ============================================================
CREATE TABLE letter_logs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id         INT UNSIGNED NULL,
    campaign_id     INT UNSIGNED NULL,
    user_id         INT UNSIGNED NULL,
    template_id     INT UNSIGNED NULL,
    letter_type     VARCHAR(100) NOT NULL,
    content         LONGTEXT NOT NULL,
    status          ENUM('generated','printed','mailed') NOT NULL DEFAULT 'generated',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead     (lead_id),
    INDEX idx_campaign (campaign_id),
    FOREIGN KEY (lead_id)      REFERENCES leads(id)             ON DELETE SET NULL,
    FOREIGN KEY (campaign_id)  REFERENCES campaigns(id)         ON DELETE SET NULL,
    FOREIGN KEY (user_id)      REFERENCES users(id)             ON DELETE SET NULL,
    FOREIGN KEY (template_id)  REFERENCES message_templates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- IMPORT JOBS
-- ============================================================
CREATE TABLE import_jobs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NULL,
    filename        VARCHAR(255) NOT NULL,
    original_name   VARCHAR(255) NOT NULL,
    source_type     VARCHAR(80)  NOT NULL DEFAULT 'custom',  -- propstream, assessor, regrid, custom
    total_rows      INT UNSIGNED NOT NULL DEFAULT 0,
    imported_rows   INT UNSIGNED NOT NULL DEFAULT 0,
    skipped_rows    INT UNSIGNED NOT NULL DEFAULT 0,
    updated_rows    INT UNSIGNED NOT NULL DEFAULT 0,
    error_rows      INT UNSIGNED NOT NULL DEFAULT 0,
    status          ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
    field_map       JSON NULL,   -- mapping of CSV columns to DB fields
    error_log       TEXT NULL,
    started_at      DATETIME NULL,
    completed_at    DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user   (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- IMPORT ROWS (individual row tracking)
-- ============================================================
CREATE TABLE import_rows (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id      INT UNSIGNED NOT NULL,
    lead_id     INT UNSIGNED NULL,
    row_number  INT UNSIGNED NOT NULL,
    raw_data    JSON NULL,
    status      ENUM('imported','updated','skipped','error') NOT NULL DEFAULT 'imported',
    error_msg   VARCHAR(500) NULL,
    INDEX idx_job    (job_id),
    INDEX idx_lead   (lead_id),
    INDEX idx_status (status),
    FOREIGN KEY (job_id)  REFERENCES import_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ACTIVITY LOGS
-- ============================================================
CREATE TABLE activity_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id     INT UNSIGNED NULL,
    user_id     INT UNSIGNED NULL,
    action      VARCHAR(100) NOT NULL,  -- 'lead_created', 'status_changed', etc.
    description TEXT NULL,
    meta        JSON NULL,              -- additional context (old/new values, etc.)
    ip_address  VARCHAR(45) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead      (lead_id),
    INDEX idx_user      (user_id),
    INDEX idx_action    (action),
    INDEX idx_created   (created_at),
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DEAL ANALYSES (AI-generated)
-- ============================================================
CREATE TABLE deal_analyses (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id         INT UNSIGNED NOT NULL,
    user_id         INT UNSIGNED NULL,
    ai_provider     VARCHAR(50) NULL,   -- 'openai', 'claude', 'manual'
    summary         TEXT NULL,
    motivation_est  TEXT NULL,
    offer_range_low DECIMAL(12,2) NULL,
    offer_range_high DECIMAL(12,2) NULL,
    repair_notes    TEXT NULL,
    rental_estimate DECIMAL(10,2) NULL,
    brrrr_potential TINYINT(1) NULL,
    flip_potential  TINYINT(1) NULL,
    seller_letter   LONGTEXT NULL,
    sms_opener      TEXT NULL,
    call_script     TEXT NULL,
    raw_prompt      TEXT NULL,
    raw_response    TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead (lead_id),
    FOREIGN KEY (lead_id)  REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DEAL CALCULATIONS (saved calculator scenarios)
-- ============================================================
CREATE TABLE deal_calculations (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id             INT UNSIGNED NOT NULL,
    user_id             INT UNSIGNED NULL,
    calc_type           ENUM('buy_hold','brrrr','flip','wholesale') NOT NULL,
    name                VARCHAR(255) NULL,
    -- Inputs
    purchase_price      DECIMAL(12,2) NULL,
    arv                 DECIMAL(12,2) NULL,
    repairs             DECIMAL(12,2) NULL,
    closing_costs       DECIMAL(12,2) NULL,
    holding_costs       DECIMAL(12,2) NULL,
    rent                DECIMAL(10,2) NULL,
    vacancy_pct         DECIMAL(5,2) NULL,
    mgmt_pct            DECIMAL(5,2) NULL,
    taxes               DECIMAL(10,2) NULL,
    insurance           DECIMAL(10,2) NULL,
    loan_amount         DECIMAL(12,2) NULL,
    interest_rate       DECIMAL(5,3) NULL,
    down_payment        DECIMAL(12,2) NULL,
    loan_term_years     TINYINT UNSIGNED NULL,
    -- Outputs (stored for quick reference)
    monthly_payment     DECIMAL(10,2) NULL,
    noi                 DECIMAL(10,2) NULL,
    cash_flow           DECIMAL(10,2) NULL,
    cap_rate            DECIMAL(5,2) NULL,
    dscr                DECIMAL(5,2) NULL,
    coc_return          DECIMAL(5,2) NULL,
    flip_profit         DECIMAL(12,2) NULL,
    mao                 DECIMAL(12,2) NULL,
    wholesale_fee       DECIMAL(12,2) NULL,
    cash_needed         DECIMAL(12,2) NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lead (lead_id),
    FOREIGN KEY (lead_id)  REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- API PROVIDERS (placeholder integration registry)
-- ============================================================
CREATE TABLE api_providers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    slug            VARCHAR(80)  NOT NULL UNIQUE,
    description     TEXT NULL,
    api_key         VARCHAR(500) NULL,   -- encrypted in production
    api_secret      VARCHAR(500) NULL,
    endpoint_url    VARCHAR(500) NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 0,
    config          JSON NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SYSTEM SETTINGS
-- ============================================================
CREATE TABLE system_settings (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    value       TEXT NULL,
    type        ENUM('string','integer','boolean','json') NOT NULL DEFAULT 'string',
    label       VARCHAR(255) NULL,
    group_name  VARCHAR(80)  NULL,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
