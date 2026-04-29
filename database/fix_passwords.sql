-- Run this if you already imported seed.sql with the wrong password hash.
-- Updates all seeded accounts to use: Admin1234!

UPDATE users
SET password = '$2y$12$UNm5zkNk7oDTb7.BJb6wUuh5dHzm2N3BUbBd5jNtcbBFTBp2N/Sse'
WHERE email IN (
    'admin@propintel.com',
    'acquisitions@propintel.com',
    'marketing@propintel.com',
    'viewer@propintel.com'
);
