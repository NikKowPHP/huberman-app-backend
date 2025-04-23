
**Huberman App Project: Cline Workspace Interaction Rules**

**Preamble:** These rules govern the interaction between the User and Cline (the AI assistant) for the implementation of the Huberman App backend. The primary goal is to ensure development strictly follows the established project documentation, proceeds methodically one task at a time, maintains an accurate `backend_implementation_todo.md`, and includes phase reviews for quality assurance.

**Core Principles:**

1.  **Documentation is Sovereign:** All implementation requests and actions performed by Cline **MUST** align strictly with the specifications detailed in the project documentation files (`app_description.md`, `architecture_pan.md`, `database_plan.md`, `api_design_documentation.md`, `complex_workflows.md`, `component_breakdown.md`, `component_interfaces_interactions.md`, `database_migrations_plan.md`, `backend_implementation_todo.md`, `security_plan.md`, etc.). Cline should reference relevant documentation when implementing or explaining.
2.  **One Task Per Request:** The User will request **only one specific, actionable task** from the `backend_implementation_todo.md` checklist in each prompt. Complex tasks identified in the checklist should already be broken down into smaller sub-items; if not, the first request might be *to* break down a larger checklist item based on the docs.
3.  **Assume No Memory:** Treat each request as potentially stateless. While context windows help, Cline should primarily rely on the explicitly provided documentation and the specific task request in the current prompt.
4.  **User Verification is Final:** Cline assists with implementation and checks, but the User is ultimately responsible for verifying the correctness and completeness of the implementation against the documentation before confirming task completion.

**Requesting Work (User -> Cline):**

5.  **Reference Specific TODO Item:** Clearly state the exact task ID or description from the `backend_implementation_todo.md` checklist that needs implementation.
    *   *Example: "Please implement the subtask: '(TDD) Write tests for `User` model creation, relationships (...), attributes, fillable properties...' under Phase 2: User Model & Migration."*
6.  **Provide Necessary Context (If Applicable):** If the task relies heavily on a specific diagram, workflow, or section in another document, briefly mention or link to it for context (assuming Cline can access linked documents or the full snapshot).
7.  **Avoid Ambiguity & Multiple Tasks:** Do not combine multiple checklist items into one request. Avoid vague instructions like "Work on authentication" or "Implement the next step."

**Implementation Process (Cline -> User):**

8.  **Acknowledge & Verify Task Understanding:** Cline will first acknowledge the requested task and confirm its understanding by referencing the TODO item and potentially the relevant documentation sections it will consult.
9.  **Consult Documentation:** Before implementation, Cline **MUST** review the specified task in `backend_implementation_todo.md` and cross-reference the relevant sections in other documentation files (e.g., database schema, API design, component interactions, complex workflows) to ensure the implementation plan is accurate and complete.
10. **Ask for Clarification:** If the task description is ambiguous, conflicts with documentation, or lacks necessary detail (e.g., a specific validation rule isn't documented), Cline **MUST** ask the User for clarification *before* proceeding with implementation.
11. **Implement According to Plan:** Implement the requested task (e.g., generate code, write test cases, create migration content) strictly adhering to the project documentation, coding standards (PSR-12), TDD principles (if specified `(TDD)`), and security plan.
12. **Provide Implementation & Explanation:** Present the generated code or output clearly. Briefly explain *how* the implementation addresses the specific task requirements and aligns with the referenced documentation. Highlight any assumptions made if clarification wasn't sought/provided.

**Completion & TODO List Update:**

13. **Confirm Implementation Against Task:** After providing the implementation, Cline should explicitly state that it believes the specific requested task is complete according to the referenced documentation and requirements.
14. **Propose TODO Update:** Cline will propose the update to the `backend_implementation_todo.md` file, showing the specific line item marked as complete (`[x]`).
    *   *Example: "Implementation complete. Propose updating TODO:\n```diff\n- [ ] (TDD) Write tests for `User` model creation...\n+ [x] (TDD) Write tests for `User` model creation...\n```"*
15. **User Verification & Confirmation:** The User **MUST** review Cline's implementation thoroughly against the task requirements and documentation. Only *after* verification, the User confirms approval.
    *   *User Response Example: "Confirmed. Please update the `backend_implementation_todo.md` as proposed."* or *"Correction needed: The test is missing an assertion for the `email_verified_at` cast. Please revise."*
16. **Update TODO on Confirmation:** Upon User confirmation, Cline will provide the updated `backend_implementation_todo.md` content reflecting the change.

**Phase Review Process:**

17. **Triggered by User:** When the User believes all tasks within a specific Phase (e.g., "Phase 2: Core User & Authentication") are marked complete (`[x]`) in the TODO list, the User will request a "Phase Review".
    *   *User Request Example: "All tasks in Phase 2 appear complete. Please perform a Phase Review for Phase 2."*
18. **Cline Phase Review:** Cline will:
    *   Re-read the description and all tasks listed under that specific Phase in `backend_implementation_todo.md`.
    *   Re-read all *relevant* sections from the *entire* project documentation set that pertain to the scope of that Phase (e.g., for Phase 2, review Auth sections in API docs, user schema in DB docs, auth workflow in complex workflows, etc.).
    *   Review the *collective implementation* provided for the tasks within that Phase (assuming it's available in the context/session history).
19. **Report Findings:** Cline will provide a summary report stating:
    *   Confirmation that all tasks within the Phase appear implemented and align with the reviewed documentation.
    *   **OR** A list of any discrepancies found, potential gaps, inconsistencies with documentation, missed requirements, or violations of principles (like security or coding standards) based on its review of the Phase's implementation against the docs.

**Handling Issues & Conflicts:**

20. **Report Conflicts:** If Cline identifies a direct conflict between different documentation files or between the documentation and a requested task during implementation, it must halt and report the conflict to the User for resolution.
21. **Query Out-of-Scope Requests:** If the User requests a task *not* present in `backend_implementation_todo.md` or functionality *not* described in the documentation, Cline must point this out and ask the User if the documentation/TODO list needs updating first.

---

By adhering to these rules, you create a structured, traceable, and quality-focused workflow that leverages the AI's ability to generate code and process information while keeping development grounded in the agreed-upon project plan and documentation.