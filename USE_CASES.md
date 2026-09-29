# Official Use Cases

This document outlines the official use cases for the application, categorized by user roles and modules. These use cases represent all possible actions that users and administrators can perform on the app, based on the application's functionality and API endpoints.

## 1. Visitor Use Cases (Unauthenticated)

* **UC-VIS-01: View Landing Page**
  * **Action:** Access the root URL (`/`).
  * **Description:** A visitor can view the main landing page to understand the application's value proposition and features.
* **UC-VIS-02: View Terms and Conditions**
  * **Action:** Access the terms and conditions page (`/terminos-y-condiciones`).
  * **Description:** A visitor can read the legal agreements and terms of using the app.
* **UC-VIS-03: Register for an Account**
  * **Action:** Access the registration page (`/register`) and submit the registration form.
  * **Description:** A visitor can create a new account by providing their details, transitioning their role to a regular User.
* **UC-VIS-04: Log In**
  * **Action:** Access the login page (`/login`) and submit credentials.
  * **Description:** A registered user can log in to access the application's authenticated features.

## 2. User Use Cases (Authenticated)

### Authentication & Onboarding
* **UC-USR-01: Log Out**
  * **Action:** Trigger the logout action (`/logout`).
  * **Description:** A user can securely end their active session.
* **UC-USR-02: Dismiss Main Tutorial**
  * **Action:** Dismiss the onboarding tutorial (`/user/dismiss-tutorial`).
  * **Description:** A user can hide the initial onboarding tutorial permanently once they are familiar with the dashboard.
* **UC-USR-03: Dismiss Ancillary Tutorial**
  * **Action:** Dismiss the ancillary analysis tutorial (`/user/dismiss-ancillary-tutorial`).
  * **Description:** A user can hide the tutorial related to analyzing ancillary/supporting documents.

### Dashboard & Configuration
* **UC-USR-04: View Dashboard**
  * **Action:** Access the main dashboard (`/dashboard`).
  * **Description:** A user can view their key metrics, recent document activity, and system status.
* **UC-USR-05: Select AI Model**
  * **Action:** Set the preferred AI model (`/dashboard/model`).
  * **Description:** A user can choose which specific AI model (e.g., Cerebras AI, Llama, etc.) to use for document processing.
* **UC-USR-06: Select LLM Provider**
  * **Action:** Set the preferred LLM provider (`/dashboard/provider`).
  * **Description:** A user can switch the underlying LLM provider (e.g., Groq, NVIDIA NIM, Cerebras, Qwen Cloud).

### Document Management
* **UC-USR-07: Upload Document**
  * **Action:** Upload a new document (`/dashboard/upload` or API `/v1/documents`).
  * **Description:** A user can upload documents (e.g., PDF, DOCX up to 50 MB) for processing and analysis.
* **UC-USR-08: Check Document Status**
  * **Action:** Check the real-time processing status of a document (`/dashboard/documents/{document}/status`).
  * **Description:** A user can monitor the progress of a document as it is being processed by the system.
* **UC-USR-09: Delete Document**
  * **Action:** Delete a document (`/dashboard/documents/{document}` or API `DELETE /v1/documents/{document}`).
  * **Description:** A user can permanently remove a document and all associated analysis data from the system.
* **UC-USR-10: List Documents**
  * **Action:** Retrieve a list of uploaded documents (API `GET /v1/documents`).
  * **Description:** A user can view a paginated list of all their uploaded documents.
* **UC-USR-11: Parse Document**
  * **Action:** Trigger parsing of a document (API `POST /v1/documents/{document}/parse`).
  * **Description:** A user can extract unstructured text and metadata from an uploaded document.
* **UC-USR-12: View Parsed Document Data**
  * **Action:** Retrieve the parsed data (API `GET /v1/documents/{document}/parsed`).
  * **Description:** A user can view the raw text and structure extracted from a document.

### Document Analysis
* **UC-USR-13: View Document Analysis**
  * **Action:** View the full analysis of a processed document (`/analisis/{document}` or API `GET /v1/documents/{document}/analyses/{analysis}`).
  * **Description:** A user can read the AI-generated insights, summaries, and identified risks.
* **UC-USR-14: View Clause-by-Clause Analysis**
  * **Action:** View the clause-by-clause breakdown of a document (`/analisis/{document}/clausulas`).
  * **Description:** A user can thoroughly examine specific contractual or legal elements identified by the AI.
* **UC-USR-15: Trigger Document Analysis**
  * **Action:** Start a new analysis (API `POST /v1/documents/{document}/analyze`).
  * **Description:** A user can initiate the LLM analysis process for a successfully parsed document.
* **UC-USR-16: List Analyses for a Document**
  * **Action:** View all analyses for a specific document (API `GET /v1/documents/{document}/analyses`).
  * **Description:** A user can view the history of analyses performed on a given document.

### Ancillary & Contextual Analysis
* **UC-USR-17: Upload Ancillary Document**
  * **Action:** Upload a supporting document for an existing analysis (`/analisis/{analysis}/ancillary` or API `POST /v1/analyses/{analysis}/supporting-documents`).
  * **Description:** A user can add supporting/ancillary documents to provide more context to an existing analysis.
* **UC-USR-18: Check Ancillary Document Status**
  * **Action:** Monitor the processing status of an ancillary document (`/analisis/{analysis}/ancillary/{ancillaryDocument}/status`).
  * **Description:** A user can track when a supporting document is ready.
* **UC-USR-19: View Contextual Analysis**
  * **Action:** View the complementary analysis incorporating ancillary documents (`/analisis/{document}/contextual/{analysis}`).
  * **Description:** A user can view how the primary document's analysis is affected by the context of supporting documents.
* **UC-USR-20: List Supporting Documents**
  * **Action:** View supporting documents attached to an analysis (API `GET /v1/analyses/{analysis}/supporting-documents`).
  * **Description:** A user can see which extra documents are contributing context to an analysis.
* **UC-USR-21: Detach Supporting Document**
  * **Action:** Remove a supporting document (API `DELETE /v1/analyses/{analysis}/supporting-documents/{document}`).
  * **Description:** A user can remove an ancillary document's context from an analysis.
* **UC-USR-22: Trigger Contextual Re-analysis**
  * **Action:** Re-analyze with context (API `POST /v1/analyses/{analysis}/analyze-with-context`).
  * **Description:** A user can trigger a new analysis that incorporates all currently attached supporting documents.

### Account Settings
* **UC-USR-23: View Settings Page**
  * **Action:** Access the configuration page (`/configuracion`).
  * **Description:** A user can view their current profile details and account preferences.
* **UC-USR-24: Update Profile Information**
  * **Action:** Change profile details like name (`/configuracion/profile`).
  * **Description:** A user can update their personal information to ensure it is current.
* **UC-USR-25: Update Avatar**
  * **Action:** Upload a new profile picture (`/configuracion/avatar`).
  * **Description:** A user can personalize their account visually with an avatar.
* **UC-USR-26: View Avatar**
  * **Action:** Retrieve the user's avatar image (`/configuracion/avatar/{user}`).
  * **Description:** The system serves the user's uploaded avatar securely.
* **UC-USR-27: Update Password**
  * **Action:** Change the account password (`/configuracion/password`).
  * **Description:** A user can securely update their password by providing their current one.
* **UC-USR-28: Delete Account**
  * **Action:** Permanently delete the user account (`/configuracion/account`).
  * **Description:** A user can remove their account and all associated data permanently from the system.

### Support
* **UC-USR-29: Submit Bug Ticket**
  * **Action:** Create a new bug report or support ticket (`/tickets`).
  * **Description:** A user can report issues or request assistance from the platform administrators.

## 3. Administrator Use Cases

*Administrators have access to all User use cases, plus the following administrative actions.*

### Dashboard & Overview
* **UC-ADM-01: View Admin Dashboard**
  * **Action:** Access the admin dashboard (`/admin`).
  * **Description:** An administrator can view system-wide metrics, active users, organization stats, and recent activities.

### User Management
* **UC-ADM-02: List Users**
  * **Action:** View the user directory (`/admin/users`).
  * **Description:** An administrator can see all registered users on the platform.
* **UC-ADM-03: Create User**
  * **Action:** Access the creation form (`/admin/users/create`) and submit (`/admin/users`).
  * **Description:** An administrator can manually provision a new user account.
* **UC-ADM-04: Edit User**
  * **Action:** Access the edit form (`/admin/users/{user}/edit`) and update (`/admin/users/{user}`).
  * **Description:** An administrator can modify a user's details, roles, or quotas.
* **UC-ADM-05: Delete User**
  * **Action:** Remove a user account (`/admin/users/{user}`).
  * **Description:** An administrator can permanently delete a user and their associated data.

### Organization Management
* **UC-ADM-06: List Organizations**
  * **Action:** View the organizations list (`/admin/organizations`).
  * **Description:** An administrator can view all organizations, their active plans, and credit usage.
* **UC-ADM-07: Toggle Organization Status**
  * **Action:** Suspend or activate an organization (`/admin/organizations/{organization}/toggle`).
  * **Description:** An administrator can enable or disable an organization's access to the platform (e.g., for billing issues).

### Analysis Auditing
* **UC-ADM-08: List Analyses**
  * **Action:** View a system-wide list of document analyses (`/admin/analyses`).
  * **Description:** An administrator can audit the analyses performed across the system for quality and usage monitoring.
* **UC-ADM-09: View Specific Analysis Details**
  * **Action:** Inspect a specific analysis (`/admin/analyses/{analysis}`).
  * **Description:** An administrator can view the detailed results and metadata of any analysis for troubleshooting or auditing purposes.

### Support & Ticketing
* **UC-ADM-10: List Bug Tickets**
  * **Action:** View all submitted bug tickets (`/admin/tickets`).
  * **Description:** An administrator can view and manage support requests and bug reports submitted by users.
* **UC-ADM-11: Resolve Bug Ticket**
  * **Action:** Mark a ticket as resolved (`/admin/tickets/{ticket}/resolve`).
  * **Description:** An administrator can update the status of a ticket once the issue has been addressed.

## 4. API & System Automated Use Cases
* **UC-SYS-01: Retrieve AI Models List**
  * **Action:** Fetch available AI models (API `GET /v1/ai-models`).
  * **Description:** The system or frontend clients can retrieve the list of configured AI models, their tiers, and credit multipliers.
