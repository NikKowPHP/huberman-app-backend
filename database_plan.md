CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,                     -- Unique identifier for the user
    name VARCHAR(255) NOT NULL,                  -- User's display name
    email VARCHAR(255) NOT NULL UNIQUE,          -- User's email address (used for login, must be unique)
    password VARCHAR(255) NOT NULL,              -- Hashed password
    email_verified_at TIMESTAMP NULL,            -- Timestamp when the user verified their email address
    profile_picture_url VARCHAR(2048) NULL,      -- URL to the user's profile picture (optional)
    remember_token VARCHAR(100) NULL,            -- For "remember me" functionality (Laravel standard)
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL                    -- For soft deletes, if enabled
);

-- Index for faster email lookups (login)
CREATE INDEX idx_users_email ON users(email);

-- Plans Table: Defines the available subscription tiers (including Free).
CREATE TABLE plans (
    id SERIAL PRIMARY KEY,                       -- Unique identifier for the plan
    name VARCHAR(255) NOT NULL UNIQUE,           -- Human-readable name (e.g., 'Free', 'Premium Monthly', 'Premium Annual')
    description TEXT NULL,                       -- Optional description of the plan
    -- Provider-specific identifiers are crucial for mapping
    stripe_product_id VARCHAR(255) NULL UNIQUE,  -- Corresponding Product ID in Stripe (if using Stripe)
    apple_product_id VARCHAR(255) NULL UNIQUE,   -- Corresponding Product ID in App Store Connect (for IAP)
    google_product_id VARCHAR(255) NULL UNIQUE,  -- Corresponding Product ID in Google Play Console (for IAP)
    features JSONB NULL,                         -- Store list of features or entitlements as JSON (flexible)
    is_active BOOLEAN NOT NULL DEFAULT TRUE,     -- Flag to activate/deactivate plans without deleting
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    -- Note: Price details are often better managed within Stripe/App Stores or a separate related table
    --       if multiple prices exist per plan (e.g., different regions, currencies).
    --       Alternatively, add stripe_price_id etc. here if simple 1 price per plan.
);

-- Index for quickly finding active plans
CREATE INDEX idx_plans_is_active ON plans(is_active);

-- Subscriptions Table: Tracks user subscriptions to specific plans.
CREATE TABLE subscriptions (
    id BIGSERIAL PRIMARY KEY,                     -- Unique identifier for the subscription record
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE, -- Link to the user
    plan_id INT NOT NULL REFERENCES plans(id) ON DELETE RESTRICT, -- Link to the subscribed plan (RESTRICT prevents deleting a plan with active subs)
    status VARCHAR(50) NOT NULL,                 -- Current status (e.g., 'active', 'trialing', 'past_due', 'canceled', 'expired', 'incomplete')
    provider VARCHAR(50) NOT NULL,               -- Source ('stripe', 'apple', 'google')
    provider_id VARCHAR(255) NOT NULL,           -- Subscription ID from the payment provider (e.g., Stripe sub_xxx, Apple original_transaction_id, Google purchaseToken)
    provider_plan_id VARCHAR(255) NULL,          -- Plan/Product ID from the provider (redundant but useful for verification)
    trial_ends_at TIMESTAMP NULL,                -- Timestamp when the trial period ends (if applicable)
    ends_at TIMESTAMP NULL,                      -- Timestamp when the current subscription period ends (renews or expires)
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL                    -- For soft deletes (e.g., keep history even if user cancels immediately)

    -- Ensure a user + provider combo doesn't have duplicate active subscription IDs from the provider
    -- Note: Uniqueness might depend on how provider IDs are handled (e.g., Google Play tokens change)
    -- A composite unique index might be needed based on specific provider behavior.
    -- UNIQUE (user_id, provider, provider_id) -- Consider implications carefully
);

-- Indexes for common lookups
CREATE INDEX idx_subscriptions_user_id ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);
CREATE INDEX idx_subscriptions_ends_at ON subscriptions(ends_at);
CREATE INDEX idx_subscriptions_provider_id ON subscriptions(provider, provider_id); -- Useful for webhook lookups

-- Episodes Table: Stores information about podcast episodes.
CREATE TABLE episodes (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(512) NOT NULL,                 -- Title of the episode
    podcast_source VARCHAR(255) NOT NULL DEFAULT 'Huberman Lab', -- Source podcast name
    episode_number INT NULL,                     -- Episode number, if applicable
    published_at DATE NULL,                      -- Original air date
    description TEXT NULL,                       -- Show notes or description
    audio_url VARCHAR(2048) NULL,                -- Link to the audio file (optional)
    source_url VARCHAR(2048) NULL,               -- Link to the original episode page/source
    duration_seconds INT NULL,                   -- Duration in seconds (optional)
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Index for sorting/filtering by publication date
CREATE INDEX idx_episodes_published_at ON episodes(published_at);

-- Protocols Table: Defines the health/performance protocols discussed.
CREATE TABLE protocols (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,           -- Name of the protocol (e.g., 'Morning Sunlight Viewing')
    description TEXT NOT NULL,                   -- Detailed explanation of the protocol
    category VARCHAR(100) NULL,                  -- Category (e.g., 'Sleep', 'Focus', 'Nutrition', 'Fitness')
    implementation_guide TEXT NULL,              -- Step-by-step guide or tips for implementation
    research_links JSONB NULL,                   -- Store relevant research paper links as JSON array/objects
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Index for filtering by category
CREATE INDEX idx_protocols_category ON protocols(category);

-- Episode_Protocol Pivot Table: Many-to-many relationship between episodes and protocols.
CREATE TABLE episode_protocol (
    episode_id BIGINT NOT NULL REFERENCES episodes(id) ON DELETE CASCADE,
    protocol_id BIGINT NOT NULL REFERENCES protocols(id) ON DELETE CASCADE,
    -- Optional: Add fields like relevance_score, timestamps_mentioned (JSONB) if needed
    PRIMARY KEY (episode_id, protocol_id) -- Composite primary key ensures uniqueness and creates index
);

-- Summaries Table: Stores summaries, key takeaways, or soundbites related to episodes.
CREATE TABLE summaries (
    id BIGSERIAL PRIMARY KEY,
    episode_id BIGINT NOT NULL REFERENCES episodes(id) ON DELETE CASCADE, -- Link to the episode
    -- Optional: protocol_id BIGINT NULL REFERENCES protocols(id) ON DELETE SET NULL, -- If a summary is specific to a protocol within an episode
    content TEXT NOT NULL,                       -- The summary text itself
    type VARCHAR(50) NOT NULL DEFAULT 'summary', -- Type ('summary', 'key_takeaway', 'soundbite', 'quote')
    -- Optional: Add start/end timestamps if linking to specific audio points
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Index for quickly finding summaries for an episode
CREATE INDEX idx_summaries_episode_id ON summaries(episode_id);

-- Notes Table: Stores user-generated notes, linked to episodes.
CREATE TABLE notes (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE, -- Link to the note author
    episode_id BIGINT NOT NULL REFERENCES episodes(id) ON DELETE CASCADE, -- Link to the related episode
    content TEXT NOT NULL,                       -- The content of the note
    is_public BOOLEAN NOT NULL DEFAULT FALSE,    -- Flag for public vs. private notes
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for common note queries
CREATE INDEX idx_notes_user_id ON notes(user_id);
CREATE INDEX idx_notes_episode_id ON notes(episode_id);
-- Composite index might be useful for fetching user's notes for a specific episode
CREATE INDEX idx_notes_user_episode ON notes(user_id, episode_id);
-- Index for finding public notes for an episode (community feature)
CREATE INDEX idx_notes_public_episode ON notes(episode_id, is_public) WHERE is_public = TRUE;

-- (Optional) User Protocol Settings/Tracking Table: If advanced tracking is added.
-- CREATE TABLE user_protocol_tracking (
--     id BIGSERIAL PRIMARY KEY,
--     user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
--     protocol_id BIGINT NOT NULL REFERENCES protocols(id) ON DELETE CASCADE,
--     tracked_at DATE NOT NULL DEFAULT CURRENT_DATE, -- Date the protocol was marked as done/tracked
--     notes TEXT NULL,                              -- Optional notes specific to this tracking instance
--     metadata JSONB NULL,                          -- For storing extra data (e.g., duration, intensity)
--     created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     UNIQUE (user_id, protocol_id, tracked_at) -- Prevent duplicate tracking per day
-- );
-- CREATE INDEX idx_user_protocol_tracking_user_protocol ON user_protocol_tracking(user_id, protocol_id);

-- Standard Laravel Tables (Optional but common):
-- These might be created automatically by Laravel migrations if used.

-- CREATE TABLE password_resets (
--     email VARCHAR(255) PRIMARY KEY,
--     token VARCHAR(255) NOT NULL,
--     created_at TIMESTAMP NULL
-- );

-- CREATE TABLE jobs (
--     id BIGSERIAL PRIMARY KEY,
--     queue VARCHAR(255) NOT NULL,
--     payload TEXT NOT NULL,
--     attempts SMALLINT NOT NULL,
--     reserved_at INT NULL,
--     available_at INT NOT NULL,
--     created_at INT NOT NULL
-- );
-- CREATE INDEX idx_jobs_queue_reserved_at ON jobs(queue, reserved_at);

-- CREATE TABLE failed_jobs (
--     id BIGSERIAL PRIMARY KEY,
--     connection TEXT NOT NULL,
--     queue TEXT NOT NULL,
--     payload TEXT NOT NULL,
--     exception TEXT NOT NULL,
--     failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
-- );

**Key Relationships Summary:**

- **User <-> Subscription:** One User has many Subscription records (potentially over time), each linking to one Plan.
- **User <-> Note:** One User has many Notes.
- **Episode <-> Note:** One Episode has many Notes.
- **Episode <-> Protocol:** Many Episodes can discuss many Protocols (via episode_protocol pivot table).
- **Episode <-> Summary:** One Episode has many Summaries.
- **Plan <-> Subscription:** One Plan can be associated with many Subscriptions.