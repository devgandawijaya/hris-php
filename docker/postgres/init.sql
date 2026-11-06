-- RiHRIS PostgreSQL Initialization Script
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Log database setup
SELECT 'RiHRIS Database Initialized' AS status;

