Okay, let's create the plan document (`api_design_documentation.md`) outlining the process and standards for creating the OpenAPI specification, and then provide a conceptual structure for the `openapi.yaml` file itself.

--- START OF FILE api_design_documentation.md ---

**Huberman App: API Design & Documentation Plan (OpenAPI)**

**1. Introduction**

This document outlines the process and standards for creating and maintaining the RESTful API contract for the Huberman App backend using the OpenAPI Specification (formerly Swagger). The resulting `openapi.yaml` file will serve as the single source of truth for API endpoints, request/response schemas, authentication, authorization requirements, and error handling. This ensures clear communication and alignment between the backend (Laravel) and frontend (React Native) development teams, facilitating efficient integration and testing.

**2. Tooling and Format**

*   **Specification Version:** OpenAPI 3.x (latest stable version recommended).
*   **Format:** YAML (`openapi.yaml`) is preferred for its readability compared to JSON.
*   **Generation/Maintenance:**
    *   **Initial Creation:** Likely manual or semi-automated based on initial controller/route definitions.
    *   **Ongoing Maintenance:** Consider using code annotations (e.g., `zircote/swagger-php` for Laravel) to generate the spec from PHP attributes/docblocks. This keeps documentation closer to the code but requires disciplined annotation. Alternatively, manual updates are feasible if rigorously maintained alongside code changes. The chosen method must be consistently applied.
    *   **Validation:** Use OpenAPI validation tools (online validators, editor plugins, CLI tools) regularly, potentially integrated into CI/CD pipelines, to ensure the spec is syntactically correct and adheres to the standard.

**3. Core Specification Structure**

The `openapi.yaml` file will utilize the following key sections:

*   `openapi`: Specifies the OpenAPI version.
*   `info`: Contains metadata (title, version, description).
*   `servers`: Defines base URLs for different environments (local, staging, production).
*   `tags`: Groups related API endpoints, typically aligned with backend modules (e.g., `Authentication`, `UserManagement`, `ContentManagement`, `NotesService`, `SubscriptionBilling`, `ProtocolEngine`, `Webhooks`).
*   `paths`: Defines all available API endpoints, including:
    *   HTTP methods (GET, POST, PUT, DELETE, etc.).
    *   Parameters (path, query, header).
    *   `requestBody`: Describes the payload for POST/PUT requests.
    *   `responses`: Defines possible HTTP status codes and their corresponding response schemas (including success and error responses).
    *   `security`: Specifies the required authentication/authorization schemes for the endpoint.
    *   `summary`, `description`, `tags`: For organization and clarity.
*   `components`: Defines reusable elements:
    *   `schemas`: Data models for request bodies and response payloads (based on `database_plan.md` and API Resources).
    *   `securitySchemes`: Defines authentication methods (e.g., Bearer Token).
    *   `responses`: Reusable response definitions (e.g., standard error responses).
    *   `parameters`: Reusable parameter definitions.

**4. Authentication (Laravel Sanctum/Passport)**

*   **Method:** Bearer Token authentication via Laravel Sanctum (preferred for SPAs/mobile apps) or Passport.
*   **Documentation:**
    *   Define a `securityScheme` named `bearerAuth` (or similar) of type `http` and scheme `bearer` in `components/securitySchemes`.
    *   Endpoints requiring authentication will include a `security` section referencing this scheme: `security: [{ bearerAuth: [] }]`.
    *   Login (`/api/login`) and registration (`/api/register`) endpoints will describe how to obtain the token in their response descriptions.

**5. Authorization & Feature Gating (Premium Features)**

*   **Enforcement:** All authorization and feature gating logic (checking for active premium subscriptions) is implemented **on the backend** (via Middleware, Policies, Service checks based on `SubscriptionBilling` module). The API documentation *reflects* these requirements but does not enforce them.
*   **Documentation:**
    *   Endpoints or specific operations requiring a Premium subscription will be clearly marked.
    *   **Method 1 (Description):** Use the `summary` or `description` field to explicitly state "Requires active Premium subscription."
    *   **Method 2 (Custom Extension - Optional):** Use a custom extension like `x-premium-required: true` on the path item or operation object for machine-readable indication, if desired.
    *   A standard `403 Forbidden` response schema (`#/components/responses/ForbiddenError`) will be referenced for cases where a non-premium user attempts to access a premium feature.

**6. Endpoint Structure & Key Areas (Based on Modules)**

Endpoints will be grouped using tags matching the modules defined in `component_breakdown.md`.

*   **`Authentication` Tag:**
    *   `POST /api/register`
    *   `POST /api/login`
    *   `POST /api/logout` (Requires `bearerAuth`)
    *   Password Reset endpoints (e.g., `POST /api/forgot-password`, `POST /api/reset-password`)
*   **`UserManagement` Tag:**
    *   `GET /api/user/profile` (Requires `bearerAuth`)
    *   `PUT /api/user/profile` (Requires `bearerAuth`)
*   **`SubscriptionBilling` Tag:**
    *   `GET /api/plans` (Lists available subscription plans - Free/Premium)
    *   `GET /api/user/subscription` (Gets current user's subscription status - Requires `bearerAuth`)
    *   Potentially endpoints to initiate checkout (provider-dependent) or fetch portal links.
*   **`ContentManagement` Tag:**
    *   `GET /api/episodes` (List episodes)
    *   `GET /api/episodes/{id}` (Get episode details, potentially including summaries)
    *   `GET /api/protocols` (List protocols - response may differ for free/premium users, documented in description)
    *   `GET /api/protocols/{id}` (Get protocol details - response details may differ, e.g., full implementation guide only for premium, documented in description)
    *   `GET /api/summaries` (Potentially filtered by episode/protocol)
*   **`NotesService` Tag:**
    *   `GET /api/notes` (List user's notes, requires `bearerAuth`)
    *   `POST /api/notes` (Create note, requires `bearerAuth` - premium limits described)
    *   `GET /api/notes/{id}` (Get specific note, requires `bearerAuth` + ownership policy check)
    *   `PUT /api/notes/{id}` (Update note, requires `bearerAuth` + ownership policy check)
    *   `DELETE /api/notes/{id}` (Delete note, requires `bearerAuth` + ownership policy check)
    *   `GET /api/episodes/{id}/public-notes` (Fetch public notes for an episode - premium feature for *viewing* or tied to note creator being premium? Clarify requirement.)
*   **`ProtocolEngine` Tag (Primarily Premium):**
    *   `GET /api/reminders` (Requires `bearerAuth`, documented as Premium)
    *   `POST /api/reminders` (Requires `bearerAuth`, documented as Premium)
    *   `PUT /api/reminders/{id}` (Requires `bearerAuth`, documented as Premium)
    *   `DELETE /api/reminders/{id}` (Requires `bearerAuth`, documented as Premium)
    *   `POST /api/tracking/log` (Log adherence, requires `bearerAuth`, documented as Premium)
    *   `GET /api/tracking/summary` (Get tracking data/streaks, requires `bearerAuth`, documented as Premium)
*   **`Webhooks` Tag:**
    *   `POST /api/webhooks/stripe`
    *   `POST /api/webhooks/apple`
    *   `POST /api/webhooks/google`
    *   (These endpoints *do not* use `bearerAuth`. Security is via signature verification, which should be noted in the description. Request bodies will match provider specifications).

**7. Request/Response Schemas (`components/schemas`)**

*   Define reusable schemas for all major data entities (`User`, `Episode`, `Protocol`, `Summary`, `Note`, `Plan`, `Subscription`, `Reminder`, `TrackingLog`, etc.) based on `database_plan.md` and how data is structured for the API (using Laravel API Resources).
*   Define standard success response wrappers if needed (e.g., `{ "data": <Schema> }`).
*   Define standard error response schemas (see below).

**8. Error Handling**

*   **Standard Structure:** Define a consistent error response structure. Example:
    ```yaml
    ErrorResponse:
      type: object
      properties:
        message:
          type: string
          description: General error message.
        errors: # Optional: Only for validation errors (422)
          type: object
          additionalProperties:
            type: array
            items:
              type: string
          description: Field-specific validation errors.
    ```
*   **HTTP Status Codes:** Document standard usage:
    *   `200 OK`: Successful GET, PUT, DELETE.
    *   `201 Created`: Successful POST.
    *   `204 No Content`: Successful DELETE where no body is returned.
    *   `400 Bad Request`: Generic client error (e.g., malformed JSON, invalid parameters not caught by validation).
    *   `401 Unauthorized`: Missing or invalid authentication token.
    *   `403 Forbidden`: Authenticated user lacks permission (e.g., policy failure, non-premium user accessing premium feature).
    *   `404 Not Found`: Resource not found.
    *   `422 Unprocessable Entity`: Validation errors (use `errors` field in response).
    *   `500 Internal Server Error`: Unexpected server-side error.
*   **Reusable Responses:** Define common error responses in `components/responses` (e.g., `NotFound`, `Unauthorized`, `Forbidden`, `ValidationError`, `InternalServerError`) referencing the `ErrorResponse` schema.

**9. API Versioning**

*   Implement API versioning from the start, likely via a URL prefix (e.g., `/api/v1/...`). Include this prefix in the `servers` definition.

**10. Maintenance and Governance**

*   The `openapi.yaml` file MUST be checked into version control (Git).
*   Pull requests modifying API-related backend code SHOULD include corresponding updates to the `openapi.yaml` file.
*   Regular reviews and validation checks (manual or automated) are necessary to keep the documentation accurate and useful.

**11. Outcome**

The final deliverable is a comprehensive, version-controlled `openapi.yaml` file located in the project repository root or a designated `/docs` directory. This file serves as the definitive contract for the Huberman App's RESTful API.

