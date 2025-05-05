# Huberman App: Frontend Implementation TODO Checklist (React Native)

**Project Goal:** Implement the React Native mobile application frontend for the Huberman App, providing an intuitive user interface aligned with the Microsoft Fluent UI aesthetic, consuming the backend API, handling state, and integrating native features like In-App Purchases and Push Notifications.

**Development Approach:** Component-Driven Development, integrating features iteratively. Testing (Unit, Component, Integration) should be incorporated throughout. Collaboration with Backend and UI/UX teams is crucial.

**Legend:**
*   `[ ]` - To Do
*   `[x]` - Done
*   `(UI)` - Depends on UI/UX Handoff (Figma)
*   `(API)` - Depends on Backend API Endpoint(s) Readiness
*   `(Test)` - Indicates steps where writing tests is highly recommended.

---

## Phase 1: Project Setup & Foundation

*   **Environment & Tooling:**
    *   `[ ]` Initialize React Native project (`npx @react-native-community/cli init hubermanAppFrontend --template react-native-template-typescript` recommended).
    *   `[ ]` Install core dependencies:
        *   `[ ]` Navigation: `@react-navigation/native`, `@react-navigation/native-stack`, `@react-navigation/bottom-tabs` (or other required navigators).
        *   `[ ]` State Management: `zustand`.
        *   `[ ]` Secure Storage: `react-native-keychain`.
        *   `[ ]` Potentially UI Library: `fluentui-react-native` (if decided upon, see `ui_ux_handoff_integration.md`).
        *   `[ ]` API Client: `axios` (or `fetch` wrapper).
    *   `[ ]` Set up project structure (`src/screens`, `src/components`, `src/navigation`, `src/services`, `src/store`, `src/assets`, `src/hooks`, `src/theme`).
    *   `[ ]` Configure ESLint & Prettier for code quality and consistency.
    *   `[ ]` Set up TypeScript configuration (`tsconfig.json`).
    *   `[ ]` Configure basic testing environment (Jest is included, add `@testing-library/react-native`).
    *   `[ ]` Establish basic Git workflow (branching strategy defined with backend team).
    *   `[ ]` Configure environment variables (e.g., using `react-native-config`) for API base URL (local, staging, prod).

*   **Core Structure & Base Components:**
    *   `[ ]` Define base theme (colors, typography, spacing) aligned with Fluent UI and Figma styles (`src/theme`). Apply theme provider if necessary (depends on UI library choice).
    *   `[ ]` (Test) Create foundational reusable components based on early UI designs/Fluent principles (e.g., `Button`, `TextInput`, `Card`, `ScreenWrapper`).
    *   `[ ]` Set up Storybook (optional but recommended) to develop and showcase reusable components in isolation.
    *   `[ ]` Implement root navigation structure (`src/navigation/RootNavigator.tsx`): differentiate between Auth stack and Main App stack based on authentication status.

---

## Phase 2: Authentication Flow (UI & API Integration)

*   **Screens:**
    *   `[ ]` (UI) Build Login screen (`src/screens/Auth/LoginScreen.tsx`).
    *   `[ ]` (UI) Build Registration screen (`src/screens/Auth/RegisterScreen.tsx`).
    *   `[ ]` (UI) Build Forgot Password screen / flow screens.
*   **API Service:**
    *   `[ ]` Implement API service functions (`src/services/api/auth.ts`) to call backend endpoints:
        *   `[ ]` `/register` (API)
        *   `[ ]` `/login` (API)
        *   `[ ]` `/logout` (API)
        *   `[ ]` `/forgot-password`, `/reset-password` (API)
*   **State Management:**
    *   `[ ]` (Test) Implement Zustand store (`src/store/authStore.ts`) to manage:
        *   `[ ]` Authentication token (`accessToken`).
        *   `[ ]` User information (basic profile, `is_premium` flag).
        *   `[ ]` Authentication status (`isAuthenticated`).
    *   `[ ]` Implement logic to securely store/retrieve token using `react-native-keychain`.
    *   `[ ]` Implement actions/logic in the store for `login`, `register`, `logout`.
*   **Integration:**
    *   `[ ]` Connect Auth screens to API service functions and state management.
    *   `[ ]` Handle loading states and display errors from API responses.
    *   `[ ]` Implement navigation logic: navigate to Main App stack on successful login/register, navigate to Auth stack on logout or initial load if not authenticated.
    *   `[ ]` (Test) Write component tests for Auth screens, mocking navigation and API calls.

---

## Phase 3: Main App Shell & Basic Content Display (Free Tier)

*   **Navigation:**
    *   `[ ]` (UI) Implement main app navigation (e.g., Bottom Tab Navigator for Protocols, Notes (placeholder), Settings).
    *   `[ ]` (UI) Implement stack navigators within tabs where needed (e.g., Protocol List -> Protocol Detail).
*   **Screens:**
    *   `[ ]` (UI) Build Protocol List screen (`src/screens/Protocols/ProtocolListScreen.tsx`).
    *   `[ ]` (UI) Build Protocol Detail screen (`src/screens/Protocols/ProtocolDetailScreen.tsx`).
    *   `[ ]` (UI) Build Settings screen shell (`src/screens/Settings/SettingsScreen.tsx`).
*   **API Service:**
    *   `[ ]` Implement API service functions (`src/services/api/content.ts`) for:
        *   `[ ]` Fetching protocols (`/protocols`) (API)
        *   `[ ]` Fetching protocol details (`/protocols/{id}`) (API)
*   **Integration:**
    *   `[ ]` Fetch and display foundational protocol list/summaries on relevant screens.
    *   `[ ]` Handle navigation between list and detail views.
    *   `[ ]` Display loading/error states for content fetching.
    *   `[ ]` (UI) Implement visual indicators/prompts for premium-only content/features based on Figma designs.
    *   `[ ]` Conditionally render limited content based on *backend API response* (backend determines free content).

---

## Phase 4: Subscription Flow (IAP Integration)

*   **IAP Setup:**
    *   `[ ]` Install `react-native-iap` and link native dependencies.
    *   `[ ]` Configure products (Premium Monthly, Premium Annual) in App Store Connect and Google Play Console. Ensure IDs match backend `plans` table identifiers.
    *   `[ ]` Implement platform-specific setup for `react-native-iap` (iOS capabilities, Android billing dependency).
*   **API Service:**
    *   `[ ]` Implement API service functions (`src/services/api/billing.ts`) for:
        *   `[ ]` Fetching available plans (`/plans`) (API)
        *   `[ ]` Fetching current user subscription status (`/user/subscription`) (API)
*   **Screens:**
    *   `[ ]` (UI) Build "Go Premium" / Subscription Offers screen. Display plans fetched from API.
*   **State Management:**
    *   `[ ]` Add subscription status (`plan`, `status`, `ends_at`) to relevant store (`authStore` or dedicated `billingStore`).
    *   `[ ]` Implement logic to periodically fetch/refresh subscription status from `/user/subscription` API.
*   **Integration:**
    *   `[ ]` Use `react-native-iap` functions to:
        *   `[ ]` Fetch product details (`getProducts`).
        *   `[ ]` Initiate purchase flow (`requestSubscription`).
    *   `[ ]` Handle IAP purchase results (success, failure, cancellation).
        *   *Note:* The primary validation happens backend via webhooks. Frontend may need to inform the backend "purchase initiated" or simply refresh user/subscription status after a short delay or upon app foregrounding to reflect changes validated by the backend. Discuss exact flow with backend team.
    *   `[ ]` Display current subscription status on Settings screen.
    *   `[ ]` Conditionally display "Upgrade" prompts or premium features based on subscription status from the state store.
    *   `[ ]` Handle IAP initialization errors and loading states.
    *   `(Test)` Write tests for store logic related to subscription status.

---

## Phase 5: Premium Features (MVP Scope)

*   **Content Display:**
    *   `[ ]` Enhance Protocol Detail screen to display *full summaries/details* if user state indicates premium (`authStore.user.is_premium` or `billingStore.isActive`). Fetch potentially richer data from `/protocols/{id}` API (API needs to return it for premium users).
*   **Custom Reminders:**
    *   `[ ]` (UI) Build Reminder List screen (Premium only).
    *   `[ ]` (UI) Build Create/Edit Reminder screen (Premium only).
    *   `[ ]` Implement API service functions (`src/services/api/reminders.ts`) for CRUD:
        *   `[ ]` `GET /reminders` (API)
        *   `[ ]` `POST /reminders` (API)
        *   `[ ]` `PUT /reminders/{id}` (API)
        *   `[ ]` `DELETE /reminders/{id}` (API)
    *   `[ ]` Fetch and display existing reminders.
    *   `[ ]` Implement form for creating/editing reminders (selecting protocol, time).
    *   `[ ]` Integrate screens with API calls, handling loading/error states.
    *   `[ ]` Ensure Reminder screens/features are only accessible/visible to premium users.

---

## Phase 6: Push Notifications Setup

*   **Firebase Setup:**
    *   `[ ]` Install `@react-native-firebase/app` and `@react-native-firebase/messaging`.
    *   `[ ]` Configure Firebase project (iOS/Android apps). Download and add config files (`GoogleService-Info.plist`, `google-services.json`).
    *   `[ ]` Implement native setup for Firebase messaging (AppDelegate, Podfile, build.gradle changes).
*   **Token Handling:**
    *   `[ ]` Implement logic to request user permission for notifications.
    *   `[ ]` Implement logic to get the FCM device token using `@react-native-firebase/messaging`.
    *   `[ ]` Implement API service function (`src/services/api/user.ts`) to send the token to the backend (`POST /api/user/device-token`) (API).
    *   `[ ]` Send token to backend upon successful login or when the token refreshes.
*   **Notification Handling:**
    *   `[ ]` Implement listeners for foreground notifications.
    *   `[ ]` Implement listeners/setup for background/quit state notification handling.
    *   `[ ]` Implement logic to display incoming reminder notifications (or let OS handle basic display).

---

## Phase 7: Refinement, Testing & QA

*   **Styling & Polish:**
    *   `[ ]` (UI) Perform visual review across all screens against Figma designs. Adjust styles for consistency and pixel-perfection (within reason).
    *   `[ ]` Ensure consistent loading indicators and empty states.
    *   `[ ]` Ensure responsive layout handling on different screen sizes/orientations.
*   **Error Handling:**
    *   `[ ]` Implement user-friendly display for API errors (e.g., toast messages, inline errors).
    *   `[ ]` Handle network connectivity issues gracefully.
*   **Testing:**
    *   `[ ]` (Test) Write/improve unit tests for utility functions, hooks, store logic.
    *   `[ ]` (Test) Write/improve component tests using `@testing-library/react-native`.
    *   `[ ]` Perform manual end-to-end testing of all user flows (Free & Premium).
*   **Accessibility (A11y):**
    *   `[ ]` Add appropriate `accessibilityLabel`, `accessibilityHint`, `accessibilityRole` props.
    *   `[ ]` Check color contrast ratios.
    *   `[ ]` Test with screen readers (VoiceOver, TalkBack).
*   **Performance:**
    *   `[ ]` Profile app performance if necessary (React Native Perf Monitor, Flipper). Optimize slow components or heavy computations.

---

## Phase 8: Build & Deployment Preparation

*   **Configuration:**
    *   `[ ]` Configure app icons and splash screens.
    *   `[ ]` Configure build settings (version codes, bundle IDs, signing certificates) for iOS and Android release builds.
    *   `[ ]` Ensure environment variable configuration works correctly for different builds (staging, production pointing to correct API URLs).
*   **App Store Setup:**
    *   `[ ]` Create app listings in App Store Connect and Google Play Console.
    *   `[ ]` Prepare required metadata (descriptions, screenshots, privacy policy URL).
*   **Build & Test:**
    *   `[ ]` Generate release builds for iOS (`.ipa`) and Android (`.aab`).
    *   `[ ]` Test release builds thoroughly on physical devices.

---

## Phase 9: Launch & Post-Launch

*   **Release:**
    *   `[ ]` Submit builds to TestFlight (iOS) / Internal Testing (Android) for final QA.
    *   `[ ]` Submit builds for App Store / Google Play review.
    *   `[ ]` Coordinate release timing with backend team.
*   **Monitoring:**
    *   `[ ]` Integrate and configure crash reporting service (Sentry, Bugsnag).
    *   `[ ]` Monitor crash reports and user feedback post-launch.
    *   `[ ]` Address critical issues promptly via patches/updates.
