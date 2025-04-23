
**Huberman App: User Stories & MVP Definition**

**1. Introduction**

This document translates the features outlined in `app_description.md` into specific User Stories using the standard "As a..., I want..., so that..." format. These stories form the basis for the product backlog. It also proposes a Minimum Viable Product (MVP) scope, focusing on delivering core value and validating the fundamental Freemium model.

These stories should be imported into a project management tool (e.g., Jira, Trello) for estimation, tracking, and prioritization.

**2. User Story Backlog**

*(Note: These stories represent functional requirements. Technical tasks like "Set up CI/CD" or "Configure database backups" are also necessary but are typically tracked separately or as sub-tasks.)*

**2.1. Core & Foundation (Applies to All Users)**

*   **Story (Onboarding):** As a New User, I want to experience a simple onboarding flow that explains the app's core purpose and how to navigate basic features (like finding protocols and summaries) so that I can quickly understand how to use the app.
*   **Story (Authentication):** As a User, I want to register for a new account using my email and a password so that I can securely access the app's features.
*   **Story (Authentication):** As a registered User, I want to log in using my email and password so that I can access my personalized data and subscription status.
*   **Story (Authentication):** As a logged-in User, I want to be able to log out of the app so that I can secure my session.
*   **Story (Authentication):** As a User who forgot their password, I want to request a password reset via email so that I can regain access to my account.
*   **Story (Navigation):** As a User, I want clear and intuitive navigation (e.g., bottom tabs, menus) so that I can easily find different sections like protocols, notes, and settings.

**2.2. Free Tier Features**

*   **Story (Content Access):** As a Free User, I want to view short summaries and brief explanations for a pre-defined set of foundational protocols so that I can understand the core concepts without a subscription.
*   **Story (Reminders):** As a Free User, I want to receive pre-set (non-customizable) push notification reminders for foundational daily protocols (e.g., Morning Sunlight) so that I can start building basic habits.
*   **Story (Notes):** As a Free User, I want to create and view a limited number of simple text notes linked to foundational protocols/episodes so that I can record my personal reflections within basic limits.
*   **Story (Premium Prompt):** As a Free User, I want to see clear indicators for premium-only content/features and prompts/information on how to upgrade so that I understand the value proposition of the Premium tier.

**2.3. Premium Tier Features**

*   **Story (Subscription Purchase):** As a User, I want to easily view the Premium subscription offers (monthly/annual pricing) and initiate the purchase process through the native App Store/Google Play interface so that I can upgrade my account.
*   **Story (Subscription Status):** As a User, I want to see my current subscription status (Free/Premium, renewal/expiry date) within the app settings so that I know my access level.
*   **Story (Content Access - Premium):** As a Premium User, I want to access the *full library* of protocols (beyond foundational ones) so that I can explore and apply the complete range of Huberman Lab recommendations.
*   **Story (Content Access - Premium):** As a Premium User, I want to access detailed explanations, step-by-step implementation guides, and curated resource links (where available) for *all* protocols so that I can fully understand the science and apply the protocols effectively.
*   **Story (Content Access - Premium):** As a Premium User, I want to read 'key takeaway' summaries for episodes/protocols so that I can quickly grasp the most critical points.
*   **Story (Reminders - Premium):** As a Premium User, I want to set *customizable* push notification reminders for *any* protocol (choosing time, frequency, specific days) so that I can get timely prompts tailored to my routine.
*   **Story (Reminders - Premium):** As a Premium User, I want to manage my custom reminders (edit time/frequency, activate/deactivate, delete) so that I can keep my prompts up-to-date.
*   **Story (Notes - Premium):** As a Premium User, I want to create *unlimited* personal notes so that I can record extensive thoughts without restriction.
*   **Story (Tracking - Premium):** As a Premium User, I want to mark specific protocols as 'completed' for a given day so that I can track my adherence.
*   **Story (Tracking - Premium):** As a Premium User, I want to see basic visualizations of my tracking data (e.g., a completion calendar view, current streak) for a selected protocol so that I can monitor my consistency.
*   **Story (Offline Access - Premium):** As a Premium User, I want to download protocol summaries, guides, and my personal notes for offline access so that I can use the app when I don't have connectivity. *(Potential Post-MVP)*
*   **Story (Advanced Notes - Premium):** As a Premium User, I want advanced organization features for notes (like tags or search) so that I can effectively manage my learning and reflections. *(Potential Post-MVP)*
*   **Story (Community Notes - Premium):** As a Premium User, I want the *option* to make specific notes public (linked to an episode/protocol) so that I can share my takeaways with the community. *(Potential Post-MVP)*
*   **Story (Community Notes - Premium):** As a Premium User, I want to view public notes shared by other premium users on specific episodes/protocols so that I can learn from the community's insights. *(Potential Post-MVP)*
*   **Story (Personalized Routines - Premium):** As a Premium User, I want to create personalized routines ('stacks') by grouping selected protocols together so that I can manage my daily/weekly practices efficiently. *(Potential Post-MVP)*

**2.4. Technical / Backend Stories**

*   **Story (Tech):** Implement backend API endpoints for user registration, login (token generation), logout, and password reset flow.
*   **Story (Tech):** Implement backend API endpoints for CRUD operations on user profiles.
*   **Story (Tech):** Implement backend API endpoints to serve content (episodes, protocols, summaries) with filtering logic based on user subscription status (Free vs. Premium).
*   **Story (Tech):** Implement backend API endpoints for CRUD operations on notes, enforcing free-tier limits and premium permissions.
*   **Story (Tech):** Implement backend API endpoints for CRUD operations on custom reminders (Premium only).
*   **Story (Tech):** Implement backend API endpoints for logging and retrieving adherence tracking data (Premium only).
*   **Story (Tech):** Implement backend webhook endpoints for Stripe/Apple/Google to receive and process subscription lifecycle events (created, renewed, canceled, expired, failed).
*   **Story (Tech):** Implement core subscription status checking logic (`SubscriptionServiceInterface::userHasActivePremiumSubscription`) including caching.
*   **Story (Tech):** Implement API middleware (`CheckPremiumAccess`) to protect premium endpoints.
*   **Story (Tech):** Implement the reminder scheduling logic (Console command + Queued Jobs) and push notification sending via Firebase Admin SDK / APNS.
*   **Story (Tech):** Define and implement all required database migrations based on `database_plan.md`.
*   **Story (Tech):** Set up project foundation: Base Laravel application, React Native project, Dockerized local development environment.

**3. Proposed Minimum Viable Product (MVP) Scope**

The MVP aims to validate the core value proposition: providing structured Huberman content and actionable reminders, with a functional path to upgrade for more comprehensive features.

**MVP Backlog:**

*   **Foundation:**
    *   All stories under "Core & Foundation" (Onboarding, Auth, Navigation).
    *   Tech: Set up project foundation (Laravel, RN, Docker).
    *   Tech: Implement basic database migrations (users, plans, subscriptions, basic content).
    *   Tech: Implement Auth API endpoints.
*   **Free Tier Experience:**
    *   Story: View short summaries/explanations for *foundational* protocols.
    *   Story: Receive *pre-set* reminders for foundational protocols.
    *   Story: See clear prompts/indicators to upgrade.
    *   Tech: Implement content API with basic free/premium filtering.
    *   Tech: Implement basic reminder sending logic for free tier.
*   **Subscription Flow:**
    *   Story: View Premium subscription offers and initiate purchase via IAP.
    *   Story: See current subscription status in settings.
    *   Tech: Implement API endpoints for plans/subscription status.
    *   Tech: Implement *full* backend webhook handling for chosen provider(s) to update `subscriptions` table correctly.
    *   Tech: Implement *frontend* IAP purchase flow (`react-native-iap`).
    *   Tech: Implement API middleware/policies for feature gating (`CheckPremiumAccess`).
*   **Core Premium Value (Validation):**
    *   Story: Access the *full library* of protocol *summaries* (unlocking more content than free tier).
    *   Story: Set *one type* of simple customizable reminder (e.g., daily at a specific time) for *any* protocol. (Demonstrates core premium reminder value).
    *   Tech: Implement custom reminder API endpoints (basic create/view/delete).
    *   Tech: Implement scheduling/sending logic for custom reminders.

**Excluded from MVP (Potential Phase 2+):**

*   Detailed explanations/implementation guides (Premium Content Depth)
*   Resource links (Premium Content Depth)
*   Key Takeaways (Premium Content Depth)
*   Advanced reminder customization (complex frequencies, snooze)
*   Note-taking (even limited Free tier might be deferred if complex)
*   Tracking features (logging adherence, visualizations)
*   Offline Access
*   Advanced Note features (unlimited, tags, search, public notes)
*   Community Features
*   Personalized Routines/Stacks
*   Password Reset (could be deferred slightly if needed, but usually core)

**4. Next Steps**

1.  Import these stories into the chosen project management tool (Jira/Trello).
2.  Refine story details and add acceptance criteria.
3.  Estimate the effort for each story (e.g., using Story Points) with the development team.
4.  Prioritize the backlog further, confirming the MVP scope based on estimations and strategic goals.
5.  Begin sprint planning based on the prioritized MVP backlog.
