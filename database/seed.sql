-- PropIntel CRM Seed Data
-- Run AFTER schema.sql

-- ============================================================
-- Default admin user  (password: Admin1234!)
-- ============================================================
INSERT INTO users (name, email, password, role, active) VALUES
('Admin User',        'admin@propintel.com',        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHdpkp0Iy', 'admin',        1),
('Acquisitions Agent','acquisitions@propintel.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHdpkp0Iy', 'acquisitions', 1),
('Marketing Manager', 'marketing@propintel.com',    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHdpkp0Iy', 'marketing',    1),
('View Only User',    'viewer@propintel.com',       '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHdpkp0Iy', 'viewer',       1);

-- ============================================================
-- API Provider placeholders
-- ============================================================
INSERT INTO api_providers (name, slug, description, is_active) VALUES
('PropStream',     'propstream',    'Property data and skip tracing',           0),
('BatchLeads',     'batchleads',    'Bulk lead data and skip tracing',          0),
('Regrid',         'regrid',        'Parcel data and GIS information',          0),
('RentCast',       'rentcast',      'Rent estimates and rental market data',    0),
('SendGrid',       'sendgrid',      'Email delivery service',                   0),
('Twilio',         'twilio',        'SMS messaging service',                    0),
('OpenAI',         'openai',        'AI analysis and content generation',       0),
('Claude (Anthropic)', 'claude',    'AI analysis and content generation',       0),
('Zillow (Unofficial)', 'zillow',   'Property value estimates',                 0),
('County Assessor', 'county_assessor', 'Local assessor parcel data',           0);

-- ============================================================
-- System settings defaults
-- ============================================================
INSERT INTO system_settings (setting_key, value, type, label, group_name) VALUES
('company_name',         'PropIntel CRM',     'string',  'Company Name',              'general'),
('company_phone',        '',                  'string',  'Company Phone',             'general'),
('company_email',        '',                  'string',  'Company Email',             'general'),
('company_address',      '',                  'string',  'Company Mailing Address',   'general'),
('sender_name',          'Real Estate Investor', 'string', 'Default Sender Name',     'outreach'),
('sendgrid_from_email',  '',                  'string',  'SendGrid From Email',       'sendgrid'),
('sendgrid_from_name',   '',                  'string',  'SendGrid From Name',        'sendgrid'),
('twilio_from_number',   '',                  'string',  'Twilio From Phone Number',  'twilio'),
('ai_provider',          'openai',            'string',  'AI Provider (openai/claude)','ai'),
('ai_model',             'gpt-4o',            'string',  'AI Model',                  'ai'),
('lead_score_auto',      '1',                 'boolean', 'Auto-score on import',      'scoring'),
('items_per_page',       '25',                'integer', 'Leads per page',            'display'),
('timezone',             'America/Los_Angeles','string', 'Timezone',                  'general');

-- ============================================================
-- Message templates
-- ============================================================
INSERT INTO message_templates (name, type, category, subject, body, variables) VALUES
(
    'Absentee Owner Letter',
    'letter',
    'absentee_owner',
    NULL,
    '{{sender_name}}
{{company_address}}

{{today_date}}

{{owner_name}}
{{mailing_address}}
{{mailing_city}}, {{mailing_state}} {{mailing_zip}}

Dear {{owner_name}},

I hope this letter finds you well. My name is {{sender_name}}, and I am a local real estate investor based in your area. I recently came across your property located at {{property_address}}, {{city}}, and I wanted to reach out directly.

I specialize in purchasing properties quickly, often in as-is condition, which means no repairs, no real estate agent commissions, and a smooth, hassle-free closing on your timeline.

If you have ever considered selling this property — whether you are managing it from a distance, dealing with difficult tenants, or simply ready to move on — I would love to have a conversation.

I can offer:
- A fair, no-obligation cash offer
- Flexible closing timeline (as fast as 7 days or as slow as 90 days)
- No repairs required — we buy as-is
- No commissions or hidden fees

Your estimated equity in this property presents a real opportunity, and I am confident we can find a number that works for everyone.

Please give me a call or send a text at {{sender_phone}} at your earliest convenience. I look forward to hearing from you.

Sincerely,

{{sender_name}}
{{sender_phone}}',
    '["owner_name","property_address","city","mailing_address","mailing_city","mailing_state","mailing_zip","sender_name","sender_phone","company_address","estimated_equity","today_date"]'
),
(
    'Pre-Foreclosure Letter',
    'letter',
    'pre_foreclosure',
    NULL,
    '{{sender_name}}
{{company_address}}

{{today_date}}

{{owner_name}}
{{mailing_address}}
{{mailing_city}}, {{mailing_state}} {{mailing_zip}}

Dear {{owner_name}},

I understand that navigating financial challenges with a property can feel overwhelming. I am reaching out because I may be able to help.

I am a local real estate investor and I purchase properties directly from homeowners — even in difficult situations. If you are behind on payments or facing foreclosure, selling your property now may be the best way to protect your credit and walk away with cash in hand.

I can close quickly, pay off your existing mortgage balance, and handle all the details so you can move forward with peace of mind.

This is completely confidential. You are under no obligation to accept any offer.

Please call or text me at {{sender_phone}} to discuss your options. Time may be limited, and I want to help.

Sincerely,

{{sender_name}}
{{sender_phone}}',
    '["owner_name","property_address","city","mailing_address","mailing_city","mailing_state","mailing_zip","sender_name","sender_phone","company_address","today_date"]'
),
(
    'Probate Letter',
    'letter',
    'probate',
    NULL,
    '{{sender_name}}
{{company_address}}

{{today_date}}

{{owner_name}}
{{mailing_address}}
{{mailing_city}}, {{mailing_state}} {{mailing_zip}}

Dear {{owner_name}},

Please accept my sincerest condolences on the passing of your loved one.

I am reaching out to let you know that I am a local real estate investor who may be able to assist you with the property at {{property_address}}. I understand that settling an estate can be a complex and time-consuming process, and dealing with a property can add additional stress during an already difficult time.

If selling the property is part of your plan, I would be honored to provide a no-obligation cash offer. I can work around your timeline and handle all the paperwork to make the process as simple as possible.

There is no pressure and no obligation. I simply want to be a resource for you when you are ready.

Please feel free to call or text me at {{sender_phone}}.

With respect and care,

{{sender_name}}
{{sender_phone}}',
    '["owner_name","property_address","city","mailing_address","mailing_city","mailing_state","mailing_zip","sender_name","sender_phone","company_address","today_date"]'
),
(
    'Tax Delinquent Letter',
    'letter',
    'tax_delinquent',
    NULL,
    '{{sender_name}}
{{company_address}}

{{today_date}}

{{owner_name}}
{{mailing_address}}
{{mailing_city}}, {{mailing_state}} {{mailing_zip}}

Dear {{owner_name}},

I am a local real estate investor and I am writing regarding the property at {{property_address}}.

I understand that tax situations can become complicated, and I want you to know that I may be able to help. I purchase properties in as-is condition and can close quickly — which means I can potentially help you resolve any outstanding tax obligations and put cash in your pocket at the same time.

I can pay delinquent taxes as part of the purchase and offer a clean, simple transaction with no real estate agents, no listing fees, and no repairs required.

If you are interested in discussing a potential sale, please call or text me at {{sender_phone}}. I promise a respectful, confidential conversation with absolutely no pressure.

Sincerely,

{{sender_name}}
{{sender_phone}}',
    '["owner_name","property_address","city","mailing_address","mailing_city","mailing_state","mailing_zip","sender_name","sender_phone","company_address","today_date"]'
),
(
    'Vacant Property Letter',
    'letter',
    'vacant',
    NULL,
    '{{sender_name}}
{{company_address}}

{{today_date}}

{{owner_name}}
{{mailing_address}}
{{mailing_city}}, {{mailing_state}} {{mailing_zip}}

Dear {{owner_name}},

I noticed that the property at {{property_address}} appears to be vacant, and I wanted to reach out directly.

Vacant properties can be a liability — maintenance costs, property taxes, insurance, and the risk of vandalism all add up quickly. I am a local real estate investor and I specialize in purchasing vacant properties in as-is condition, often with a very quick closing timeline.

If you are ready to sell, I can make the process simple and fast. No real estate agent, no repairs, no showings — just a straightforward cash transaction.

I would love to talk with you about your situation and see if we can work something out that benefits you. Please call or text me at {{sender_phone}} whenever it is convenient.

Sincerely,

{{sender_name}}
{{sender_phone}}',
    '["owner_name","property_address","city","mailing_address","mailing_city","mailing_state","mailing_zip","sender_name","sender_phone","company_address","today_date"]'
),
(
    'Tired Landlord Letter',
    'letter',
    'tired_landlord',
    NULL,
    '{{sender_name}}
{{company_address}}

{{today_date}}

{{owner_name}}
{{mailing_address}}
{{mailing_city}}, {{mailing_state}} {{mailing_zip}}

Dear {{owner_name}},

I am a local real estate investor and I wanted to reach out about your rental property at {{property_address}}.

Managing rental properties can be rewarding, but it can also be exhausting — dealing with tenants, maintenance calls, vacancies, and everything in between. If you have been thinking about selling and moving on, I would love to talk.

I purchase rental properties directly from landlords, even with tenants in place. No need to wait for leases to expire or deal with repairs. I handle everything.

With your estimated equity of {{estimated_equity}}, you may be in a great position to cash out and simplify your life. Let me make you a no-obligation offer.

Call or text me at {{sender_phone}} — I look forward to the conversation.

Sincerely,

{{sender_name}}
{{sender_phone}}',
    '["owner_name","property_address","city","mailing_address","mailing_city","mailing_state","mailing_zip","sender_name","sender_phone","company_address","estimated_equity","today_date"]'
),
(
    'SMS - Initial Contact',
    'sms',
    'initial_contact',
    NULL,
    'Hi {{owner_name}}, this is {{sender_name}}. I''m a local real estate investor and I''m interested in your property at {{property_address}}. Would you be open to a quick conversation? Reply STOP to opt out.',
    '["owner_name","property_address","sender_name"]'
),
(
    'SMS - Follow-Up',
    'sms',
    'follow_up',
    NULL,
    'Hi {{owner_name}}, following up on my message about {{property_address}}. If you''re ever interested in a cash offer with a quick close, I''d love to chat. - {{sender_name}} {{sender_phone}}',
    '["owner_name","property_address","sender_name","sender_phone"]'
),
(
    'Email - Initial Outreach',
    'email',
    'initial_contact',
    'Regarding Your Property at {{property_address}}',
    '<p>Dear {{owner_name}},</p>
<p>My name is {{sender_name}}, and I am a local real estate investor. I came across your property at <strong>{{property_address}}</strong> and wanted to reach out directly.</p>
<p>I specialize in purchasing properties quickly and in as-is condition. If you have ever considered selling — for any reason — I would love to discuss making you a fair, no-obligation cash offer.</p>
<ul>
<li>Close in as little as 7 days</li>
<li>No repairs needed</li>
<li>No real estate commissions</li>
<li>Your timeline, your terms</li>
</ul>
<p>Please reply to this email or call me at <strong>{{sender_phone}}</strong> to start the conversation.</p>
<p>Best regards,<br>{{sender_name}}<br>{{sender_phone}}</p>',
    '["owner_name","property_address","sender_name","sender_phone","estimated_equity"]'
);

-- ============================================================
-- Sample leads (for testing)
-- ============================================================
INSERT INTO leads (address, city, state, zip, county, apn, property_type, status, lead_score,
    is_absentee_owner, is_vacant, is_high_equity, is_pre_foreclosure, is_tax_delinquent,
    is_probate, is_tired_landlord, created_by)
VALUES
('1234 Oak Street',     'Yakima',      'WA', '98901', 'Yakima', '181330-41012', 'Single Family', 'New',         72, 1, 1, 1, 0, 0, 0, 0, 1),
('567 Maple Ave',       'Yakima',      'WA', '98902', 'Yakima', '181330-12345', 'Single Family', 'Researching', 65, 1, 0, 1, 0, 1, 0, 0, 1),
('890 Pine Rd',         'Selah',       'WA', '98942', 'Yakima', '181330-99001', 'Single Family', 'New',         58, 1, 0, 0, 1, 0, 0, 0, 1),
('321 Elm Court',       'Union Gap',   'WA', '98903', 'Yakima', '181330-55432', 'Multi Family',  'Contacted',   81, 1, 0, 1, 0, 0, 0, 1, 1),
('654 Cedar Lane',      'Yakima',      'WA', '98908', 'Yakima', '181330-77812', 'Single Family', 'Follow-Up',   90, 1, 1, 1, 1, 1, 0, 0, 1),
('987 Birch Blvd',      'Naches',      'WA', '98937', 'Yakima', '181330-23456', 'Single Family', 'New',         45, 0, 0, 0, 0, 1, 0, 0, 1),
('147 Walnut Way',      'Yakima',      'WA', '98901', 'Yakima', '181330-34567', 'Condo',         'New',         38, 1, 0, 0, 0, 0, 1, 0, 1),
('258 Spruce Street',   'Moxee',       'WA', '98936', 'Yakima', '181330-45678', 'Single Family', 'Dead',        20, 0, 0, 0, 0, 0, 0, 0, 1);

-- Insert property details for sample leads
INSERT INTO lead_property_details (lead_id, beds, baths, sqft, lot_size, year_built,
    estimated_value, estimated_rent, loan_balance, equity_estimate, last_sale_date, last_sale_price)
VALUES
(1, 3, 2.0, 1450, '0.18 acres', 1978, 285000, 1800, 95000,  190000, '2005-06-15', 120000),
(2, 4, 2.5, 2100, '0.25 acres', 1992, 375000, 2200, 180000, 195000, '2010-03-22', 210000),
(3, 3, 1.0, 1200, '0.15 acres', 1965, 220000, 1500, 145000,  75000, '2018-09-10', 195000),
(4, 6, 3.0, 3200, '0.30 acres', 1985, 520000, 3200,  90000, 430000, '2001-11-01',  95000),
(5, 3, 2.0, 1580, '0.20 acres', 1970, 310000, 1900,  40000, 270000, '1998-07-04',  65000),
(6, 2, 1.0, 980,  '0.12 acres', 1955, 175000, 1100, 120000,  55000, '2019-01-15', 162000),
(7, 2, 2.0, 1050, NULL,         2005, 210000, 1400, 155000,  55000, '2020-05-20', 185000),
(8, 3, 1.5, 1350, '0.17 acres', 1975, 240000, 1600, 200000,  40000, '2021-08-30', 225000);

-- Insert owner details for sample leads
INSERT INTO lead_owner_details (lead_id, owner_name, owner_first_name, owner_last_name,
    mailing_address, mailing_city, mailing_state, mailing_zip, owner_occupied, out_of_state_owner, ownership_years)
VALUES
(1, 'Robert & Linda Martinez', 'Robert',   'Martinez', '4500 Pacific Ave',    'Tacoma',    'WA', '98444', 0, 0, 19),
(2, 'James T. Wilson',         'James',    'Wilson',   '8812 Ocean Blvd #4',  'San Diego', 'CA', '92101', 0, 1, 14),
(3, 'Susan K. Thompson',       'Susan',    'Thompson', '890 Pine Rd',          'Selah',    'WA', '98942', 1, 0,  6),
(4, 'Nguyen Family Trust',     'Thanh',    'Nguyen',   '12 Harbor View Dr',   'Bellevue',  'WA', '98004', 0, 0, 23),
(5, 'Harold D. Johnson',       'Harold',   'Johnson',  '654 Cedar Lane',      'Yakima',   'WA', '98908', 1, 0, 26),
(6, 'Patricia M. Davis',       'Patricia', 'Davis',    '1901 Greenview Ct',   'Portland',  'OR', '97201', 0, 1,  5),
(7, 'Estate of Frank Kowalski','Frank',    'Kowalski', '258 Spruce Street',   'Moxee',    'WA', '98936', 0, 0,  3),
(8, 'Michael R. Brown',        'Michael',  'Brown',    '987 Birch Blvd',      'Naches',   'WA', '98937', 1, 0,  3);
