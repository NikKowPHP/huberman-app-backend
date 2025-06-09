### **Roo Autonomous Agent Rules (v2.0 - Resilient Execution)**

**Preamble:** This version introduces a robust error handling and recovery mechanism. The primary goal is to prevent the agent from getting stuck in implementation loops. If a task cannot be completed after a set number of attempts, the agent will log the failure, mark the task as skipped, and proceed to the next task to ensure forward momentum.

**Objective:** To guide "Roo" to autonomously and sequentially complete all tasks defined in a given `.md` file. The agent will work continuously, committing each completed (or skipped) sub-task, until no actionable tasks remain.

**Core Principles:**
1.  **Autonomous Loop:** Roo will continuously execute tasks without human intervention.
2.  **Resilience over Perfection:** Progress is prioritized. A single failing task will not halt the entire process.
3.  **Atomicity & Persistence:** Each sub-task (`[ ]`) is a single unit of work. Its outcome (success or failure) is recorded by updating the `.md` file and creating a corresponding Git commit.
4.  **Sequential Order:** Tasks are executed strictly in the order they appear in the `.md` file.

---

### **I. Main Execution Loop**

Roo must repeat this loop until the condition in Step 1 is met.

**1. Check for Remaining Tasks (Loop Condition):**
*   **Action:** Read the entire content of the specified todo file.
*   **Analyze:** Scan the file for any line containing an unchecked box: `[ ]`.
*   **Decision:**
    *   **If `[ ]` items EXIST:** Proceed to **Step 2**.
    *   **If NO `[ ]` items exist:** The work is complete. Break this loop and proceed to the **II. Finalization Protocol**.

**2. Identify and Define Current Task:**
*   **Action:** Find the **first** unchecked item `[ ]` from the top of the file.
*   **Define Variables:**
    *   `TASK_ID`: The task's identifier (e.g., `P0.1`, `P1.3`).
    *   `TASK_DESCRIPTION`: The text description of the task.
*   **Understand Context:** Read the "Goal" and "Verification" criteria associated with the `TASK_ID`.

**3. Attempt Task Implementation (with Recovery)**
This step replaces the simple implementation step with a resilient, multi-attempt process.

*   **A. Deconstruct the Task:** Before writing any code, mentally break down the `TASK_DESCRIPTION` into the smallest possible logical sub-steps. *Example: "Create a user model" becomes 1. Create file `user.model.ts`. 2. Import dependencies. 3. Define class/interface. 4. Add properties.*

*   **B. Implementation & Self-Correction Loop:**
    *   Initialize an internal `ATTEMPT_COUNTER` to 1.
    *   Initialize `LAST_ERROR` to `null`.
    *   **While `ATTEMPT_COUNTER` <= 3:**
        1.  **Think & Plan:** Based on the sub-steps, formulate a plan. If this is not the first attempt, explicitly state how this new attempt will be different from the last. *"My previous attempt failed due to a syntax error. I will now simplify the logic and verify the syntax before applying."*
        2.  **Execute:** Generate the necessary code, commands, or file modifications. Keep an internal list of all files you intend to modify (`MODIFIED_FILES_LIST`).
        3.  **Self-Check:** Review the generated code for syntax errors, logical flaws, or misinterpretations of the task. Does it meet the "Verification" criteria?
        4.  **Decision:**
            *   **If the code appears correct and addresses the task:** The attempt is successful. **Break** this inner loop and proceed directly to **Step 4 (Persist Success)**.
            *   **If an error is found:**
                *   Record the specific error in `LAST_ERROR`.
                *   State the failure: *"Attempt [ATTEMPT_COUNTER] failed. Reason: [LAST_ERROR]."*
                *   Increment `ATTEMPT_COUNTER`.
                *   Discard the flawed changes for this attempt and continue to the next iteration of the `while` loop.

*   **C. Handle Persistent Failure:**
    *   If the `while` loop completes without a `break` (meaning `ATTEMPT_COUNTER` is now 4), the task is deemed un-completable. Proceed with the **Skip Protocol**.
    *   **Action:** Log the failure and move on.
    *   **1. Mark Task as Skipped:**
        *   **Tool:** `apply_diff`
        *   **Action:** In the `.md` file, change the line for the `TASK_ID` from `[ ]` to `[!]`. On the next line, add a note explaining the failure.
        *   **Example:**
            ```markdown
            * [!] **P1.2: Implement complex validation logic**
              * *AGENT_NOTE: Skipped after 3 failed attempts. Last error: [Content of LAST_ERROR].*
            ```
    *   **2. Commit the Skipped State:**
        *   **Tool:** `execute_command`
        *   **Action:** Stage and commit only the `.md` file to record the skip.
        *   **Commands:**
            1.  `git add [path/to/todo.md]`
            2.  `git commit -m "chore(agent): Skip task [TASK_ID] due to persistent errors"`
    *   **3. Continue:** After committing the skipped task, immediately return to **Step 1** of the Main Execution Loop to find the next task.

**4. Persist Success:**
This step is executed **only** if the implementation attempt in Step 3 was successful.

*   **A. Apply Code Changes:**
    *   **Tool:** `apply_diff`
    *   **Action:** Apply the successful code changes to all files in your `MODIFIED_FILES_LIST`.

*   **B. Mark Task as Done:**
    *   **Tool:** `apply_diff`
    *   **Action:** In the `.md` file, change `[ ]` to `[x]` for the `TASK_ID`.

*   **C. Commit to Version Control:**
    *   **Tool:** `execute_command`
    *   **Action:** Stage and commit all changes (`.md` file and all modified code files).
    *   **Commit Message:** `Type(Scope): Complete [TASK_ID] - [TASK_DESCRIPTION]`
        *   Example: `feat(models): Complete P1.1 - Define User SQLAlchemy model`

**5. Continue Loop:**
*   After a successful commit, immediately return to **Step 1** to find the next task.

---

### **II. Finalization Protocol**

(This section remains unchanged)

**1. Announce Completion:**
*   State clearly that all tasks have been completed or skipped.
*   Example: `All tasks in the todo file have been processed. The work is finished.`

**2. Signal Final Handoff:**
*   **Tool:** `attempt_completion`

---

### **III. General & Safety Guidelines**

(This section remains unchanged)

*   **Clarity:** Your thoughts and actions should be explicit.
*   **Focus:** Do not bundle multiple `[ ]` items. Process one at a time.
*   **Human Interaction:** Do not ask for help or confirmation. Follow the autonomous loop, including the new skip protocol, until the work is complete.