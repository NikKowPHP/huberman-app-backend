
**Huberman App: Finalized Technology Versions & Key Libraries**

**1. Introduction**

This document specifies the finalized versions for the core technologies and selected key third-party libraries to be used in the Huberman App project. These choices aim to provide a stable, secure, and maintainable foundation for development, based on the architectural decisions (`architecture_pan.md`) and implementation plan (`detailed_plan.md`). This information will directly inform the `composer.json` (backend) and `package.json` (frontend) files and serve as a reference point for developers.

**2. Core Technology Versions**

*   **PHP:**
    *   **Version:** `8.2+`
    *   **Justification:** Provides modern language features and performance improvements. Required by Laravel 11.x. Aim for the latest stable 8.2.x or 8.3.x patch release available at project start.
*   **Laravel Framework:**
    *   **Version:** `11.x`
    *   **Justification:** Latest major stable release (as of Q1/Q2 2024), offering the longest support window and latest features. Ensures access to up-to-date ecosystem packages.
*   **Database:**
    *   **Version:** `PostgreSQL 17.x`
    *   **Justification:** Latest stable major release offering advanced features and performance. *Note: Verify compatibility and availability with the chosen managed database provider (e.g., AWS RDS, Google Cloud SQL) before final provisioning.* If the latest isn't available, fall back to the latest supported major version (e.g., 15.x).
*   **Cache / Queue Broker:**
    *   **Version:** `Redis 7.x`
    *   **Justification:** Current, high-performance, stable version widely supported for caching and queuing.
*   **Frontend Runtime:**
    *   **Version:** `Node.js 20.x (LTS)`
    *   **Justification:** Use the current Long-Term Support (LTS) version for stability and extended support, required for React Native tooling (Metro, CLI).
*   **Frontend Framework:**
    *   **Version:** `React Native (Latest Stable)`
    *   **Justification:** Use the latest stable release available at project kickoff to leverage the newest features, performance improvements, and security patches. Version number to be confirmed at initialization (e.g., `0.73.x` or newer).

**3. Key Libraries Selection**

**3.1. Backend (Laravel - `composer.json`)**

*   **Payment Processing:**
    *   **Library:** `laravel/cashier-stripe`
    *   **Version:** `^15.0` (or version compatible with Laravel 11.x)
    *   **Justification:** Official Laravel package providing a fluent interface for Stripe's subscription billing services, simplifying integration. *Chosen over Paddle for this document, assuming Stripe preference/fit.*
*   **API Authentication:**
    *   **Library:** `laravel/sanctum`
    *   **Version:** Bundled with Laravel 11.x (or latest compatible)
    *   **Justification:** Official lightweight system for API token authentication, suitable for mobile apps and SPAs. Preferred over Passport for this use case unless OAuth server features are explicitly needed.

**3.2. Frontend (React Native - `package.json`)**

*   **In-App Purchases:**
    *   **Library:** `react-native-iap`
    *   **Version:** `Latest Stable`
    *   **Justification:** De facto standard community library for handling In-App Purchases across both iOS and Android platforms. Requires careful platform-specific setup.
*   **Push Notifications:**
    *   **Library:** `@react-native-firebase/app` & `@react-native-firebase/messaging`
    *   **Version:** `Latest Stable`
    *   **Justification:** Official Firebase modules for React Native. Provides a robust, cross-platform solution for handling push notifications via Firebase Cloud Messaging (FCM), which handles delivery to both APNS (iOS) and Android. Assumes Firebase project setup.
*   **State Management:**
    *   **Library:** `zustand`
    *   **Version:** `Latest Stable`
    *   **Justification:** A small, fast, and scalable state-management solution using simplified flux principles. Chosen for its simplicity and minimal boilerplate compared to Redux, potentially suitable for the app's expected state complexity.
*   **Navigation:**
    *   **Library:** `react-navigation` (`@react-navigation/native`, `@react-navigation/native-stack`, etc.)
    *   **Version:** `Latest Stable`
    *   **Justification:** The standard and most widely used library for handling navigation and screen transitions in React Native applications.

**4. Documentation and Updates**

*   The exact versions used will be locked in the respective `composer.lock` and `package-lock.json` files.
*   The primary `composer.json` and `package.json` files will reflect the target version constraints (e.g., `^11.0` for Laravel).
*   This document (`technology_versions_libraries.md`) serves as the initial decision record. It can be formalized into an Architecture Decision Record (ADR) if desired.
*   The project's main `README.md` should ideally include a summary of the core technology stack and versions for quick reference.
*   Dependency versions should be periodically reviewed and updated (e.g., using `composer update`, `npm update`, `Dependabot`) following appropriate testing procedures to incorporate security patches and improvements.

**5. Conclusion**

These technology and library choices provide a modern, robust, and well-supported stack for the Huberman App. Finalizing these versions allows the development team to proceed with project setup and initial implementation with clarity and consistency.
