
**Huberman App: Initial Content Strategy & Management Plan**

**1. Introduction**

This document outlines the strategy for sourcing, preparing, and ingesting the initial content required for the Huberman App backend, particularly for the Minimum Viable Product (MVP). This includes foundational protocols, summaries, and other necessary data points defined in `app_description.md` and `user_stories_mvp.md`. The goal is to establish a clear process for populating the database using Laravel Seeders.

**2. Initial Content Scope (MVP Focus)**

Based on the MVP definition, the initial content required includes:

*   **Subscription Plans:** Definition of the "Free" tier and at least one "Premium" tier (e.g., Monthly/Annual) with their identifiers (e.g., `Premium_Monthly`). Provider-specific IDs (Stripe, Apple, Google) will be needed later but placeholders can be used initially in seeders.
*   **Foundational Protocols:** A selected subset of core protocols discussed frequently in the Huberman Lab podcast (e.g., Morning Sunlight Viewing, Cold Exposure, Foundational Fitness Protocols). For each:
    *   Unique Name
    *   Category (e.g., Sleep, Focus, Fitness)
    *   Brief Description (Accessible to Free users)
    *   *(Optional MVP)* Placeholder/basic Implementation Guide (Premium access)
*   **Summaries for Foundational Protocols:** Concise summaries linked to the foundational protocols above.
*   **Placeholder Episodes:** A few basic episode entries might be needed to link summaries and notes to, even if full episode data isn't part of the MVP content focus.

**3. Content Source & Curation**

*   **Source Material:** The primary source for protocol and summary content will be the public Huberman Lab podcast episodes and potentially official associated materials (e.g., summaries on the Huberman Lab website, newsletters).
*   **Curation Process:**
    *   **Responsibility:** A designated **"Content Curator"** role is required. This role is responsible for listening to/reviewing source material, extracting key information, writing descriptions and summaries, and ensuring scientific accuracy aligns with the source. *(This role needs to be assigned - could be a project manager, a subject matter expert, or the client)*.
    *   **Extraction:** The Curator will identify the foundational protocols for the MVP, extract their names, core principles (for description), and key takeaways (for summaries).
    *   **Writing:** The Curator will write the actual `description` and `summary` text, adhering to a consistent tone and style suitable for the app. Accuracy and clarity are paramount.

**4. Content Preparation & Formatting**

*   **Format:** Content curated should be prepared in **Markdown (`.md`)** format. This provides basic text formatting (headings, lists, bold/italic) while remaining easy for developers to parse and integrate into seeders.
*   **Structure:** Separate Markdown files should be created for each protocol and potentially each summary, or grouped logically (e.g., one file per protocol containing its description and summary). A consistent structure within the files is recommended.
    *   *Example `morning_sunlight.md` structure:*
        ```markdown
        # Protocol: Morning Sunlight Viewing

        **Category:** Sleep, Circadian Rhythm

        **Description:**
        Brief explanation of the importance of viewing sunlight shortly after waking...

        **Summary:**
        Key takeaway points about the protocol...

        **(Premium) Implementation Guide:**
        Step-by-step guide (can be basic/placeholder for MVP)...
        ```
*   **Review:** Before handing off to development, the curated and formatted Markdown content should be reviewed for accuracy, clarity, consistency, and typos (potentially by another team member or the client).

**5. Ingestion Format (for Seeders)**

*   **Method:** The prepared content will be directly embedded into PHP arrays within dedicated Laravel Seeder classes (e.g., `FoundationalProtocolsSeeder.php`, `SummariesSeeder.php`).
*   **Rationale:** For the initial, relatively small MVP dataset, embedding directly in PHP arrays is the simplest method. It avoids the need for parsing external files (like JSON or CSV) during the seeding process. If the initial dataset becomes very large, transitioning to reading from JSON files stored alongside the seeders can be considered.
*   **Data Structure in Seeders:** The seeders will translate the Markdown content into the structure required by the Eloquent models (`Protocol`, `Summary`, `Episode`). Markdown text can be stored directly in the appropriate database fields (`description`, `content`, `implementation_guide`).

**6. Ingestion Mechanism (Laravel Seeders)**

*   **Implementation:** Backend developers will create the necessary Seeder classes within the `database/seeders/` directory.
*   **Specific Seeders:**
    *   `PlanSeeder.php`: Creates the 'Free' and 'Premium' plan records. Should use `updateOrCreate` to avoid duplicates on re-seeding.
    *   `EpisodeSeeder.php`: Creates placeholder Episode records if needed for relationships.
    *   `FoundationalProtocolsSeeder.php`: Creates the initial `Protocol` records from the prepared Markdown content.
    *   `SummariesSeeder.php`: Creates the initial `Summary` records, linking them to the appropriate `Protocol` or `Episode` IDs created in previous seeders.
    *   `EpisodeProtocolSeeder.php`: Links Episodes and Protocols in the pivot table if necessary for MVP structure.
*   **Execution:** Seeders will be executed using `php artisan db:seed`. They will be called sequentially from the main `DatabaseSeeder.php` file to ensure relational integrity (e.g., create protocols before summaries that link to them).
*   **Environment:** Seeders should be designed to run safely in development environments. Running seeders in staging/production needs careful consideration (usually only done for initial setup or specific reference data like Plans).

**7. Responsibilities**

*   **Content Curator:** Identify source material, extract information, write/format descriptions & summaries in Markdown, review for accuracy.
*   **Backend Developer:** Create/update Seeder classes, integrate Markdown content into PHP arrays within seeders, ensure seeders run correctly and populate the database as expected, handle relationships.
*   **QA / PM / Client:** Review the content *as it appears in the running application* (after seeding) to ensure it displays correctly and matches requirements.

**8. Future Content Management**

*   **Limitation of Seeders:** Seeders are primarily for initial data setup and developer environments. They are **not** a suitable mechanism for ongoing content updates by non-developers in a production environment.
*   **Post-MVP:** A separate strategy and implementation plan will be required for ongoing content management. This will likely involve developing an Admin Panel or integrating a Headless CMS to allow the Content Curator or other authorized users to add, edit, and manage protocols, episodes, and summaries without requiring code changes and deployments.

**9. Conclusion**

This plan outlines a clear process for handling the initial content load for the Huberman App MVP. By defining the source, curation process, formatting (Markdown), and ingestion mechanism (Laravel Seeders directly using PHP arrays), we can ensure the necessary foundational content is reliably populated for development, testing, and the initial launch. The need for a more robust, long-term content management solution post-MVP is acknowledged.
