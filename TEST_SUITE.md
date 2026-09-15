# Application Test Suite Documentation

This document provides an overview of the automated test suite. It categorizes test files and briefly explains what each test file covers.

## Feature - API

### `tests/Feature/Api/V1/DocumentAnalysisControllerTest.php`

Tests API endpoints responsible for triggering and managing document analyses.

**Tests:**
- `test_store_triggers_analysis_job`
- `test_store_validates_request`
- `test_show_returns_analysis_results`
- `test_show_returns_404_if_not_found`
- `test_show_returns_403_if_unauthorized`

### `tests/Feature/Api/V1/DocumentParsingControllerTest.php`

Tests API endpoints responsible for parsing and processing uploaded documents.

**Tests:**
- `test_parse_processes_document`
- `test_parse_validates_request`
- `test_status_returns_parsing_progress`

## Feature - Admin

### `tests/Feature/Admin/AdminAccessTest.php`

Tests access control and authorization for administrative areas.

**Tests:**
- `test_non_admin_cannot_access_admin_dashboard`
- `test_non_admin_cannot_access_admin_metrics`
- `test_non_admin_cannot_access_admin_users_list`
- `test_admin_can_access_admin_dashboard`
- `test_admin_can_access_admin_metrics`
- `test_admin_can_access_admin_users_list`

### `tests/Feature/Admin/AdminMetricsTest.php`

Verifies that platform-wide metrics (users, documents, LLM usage) are calculated and displayed correctly for administrators.

**Tests:**
- `test_platform_metrics_are_calculated_correctly`

### `tests/Feature/Admin/AdminUserManagementTest.php`

Tests administrative capabilities to manage user accounts, roles, and impersonation.

**Tests:**
- `test_admin_can_view_user_details`
- `test_admin_can_update_user_role`
- `test_admin_can_delete_user`
- `test_admin_cannot_delete_themselves`
- `test_admin_can_impersonate_user`
- `test_admin_can_stop_impersonating`

## Feature - Analysis

### `tests/Feature/AncillaryAnalysisTest.php`

Tests the functionality of secondary or supporting document analysis features.

**Tests:**
- `test_user_can_view_ancillary_analysis_page`
- `test_user_can_attach_supporting_document`
- `test_user_can_remove_supporting_document`
- `test_user_can_trigger_contextual_reanalysis`

## Feature - Authentication

### `tests/Feature/Auth/RegisterTest.php`

Verifies user registration flows, validation rules, and organization creation.

**Tests:**
- `test_registration_screen_can_be_rendered`
- `test_new_user_can_register_successfully`
- `test_new_user_can_register_without_explicit_organization_name`
- `test_user_cannot_register_with_existing_email`
- `test_user_cannot_register_with_mismatched_password_confirmation`
- `test_user_cannot_register_with_short_password`
- `test_user_cannot_register_without_accepting_terms`
- `test_authenticated_user_is_redirected_from_register_screen`

## Feature - Bug Tickets

### `tests/Feature/BugTickets/BugTicketTest.php`

Tests the submission, viewing, and resolution of user-reported bug tickets.

**Tests:**
- `test_user_can_submit_bug_ticket`
- `test_admin_can_view_bug_tickets_list`
- `test_admin_can_resolve_bug_ticket`
- `test_non_admin_cannot_resolve_bug_ticket`

## Feature - Dashboard

### `tests/Feature/DashboardMetricsServiceTest.php`

Tests the service responsible for calculating user and organization metrics displayed on the dashboard.

**Tests:**
- `test_for_organization_calculates_metrics_correctly`
- `test_query_count_and_performance_benchmark`

### `tests/Feature/DashboardProviderTest.php`

Tests user preferences related to LLM provider selection on the dashboard.

**Tests:**
- `test_user_can_switch_llm_provider_on_dashboard`
- `test_dashboard_renders_cleanly_without_deprecated_provider_selector`

### `tests/Feature/DashboardTest.php`

Verifies dashboard rendering and feature availability based on user subscription tiers.

**Tests:**
- `test_free_user_sees_standard_dashboard`
- `test_pro_user_sees_pro_dashboard`
- `test_demo_pro_user_can_login`

## Feature - Documents

### `tests/Feature/DocumentDeleteTest.php`

Tests document deletion workflows, cascading deletes, and automated pruning.

**Tests:**
- `test_user_can_delete_document_and_associated_analysis_in_cascade`
- `test_cannot_delete_another_organizations_document`
- `test_prune_free_documents_command_deletes_free_documents_older_than_7_days`

## Feature - General

### `tests/Feature/ErrorPagesTest.php`

Ensures custom error pages (404, 419) render correctly.

**Tests:**
- `test_custom_419_error_view_renders`
- `test_custom_404_error_view_renders`

### `tests/Feature/ExampleTest.php`

Basic application health check.

**Tests:**
- `test_the_application_returns_a_successful_response`

### `tests/Feature/LandingPricingTest.php`

Tests that pricing and tier information is displayed correctly on the landing page.

**Tests:**
- `test_landing_page_renders_updated_prices_and_ai_credits_card`

### `tests/Feature/TermsAndConditionsTest.php`

Ensures the Terms and Conditions pages are accessible.

**Tests:**
- `test_terms_and_conditions_page_is_accessible`
- `test_landing_page_has_terms_and_conditions_link`

## Feature - Security

### `tests/Feature/Security/Section1CriticalPrioritiesTest.php`

Validates critical security priorities including cross-tenant data isolation, authorization, and rate limiting.

**Tests:**
- `test_tenant_a_cannot_read_tenant_b_document`
- `test_tenant_a_document_list_does_not_include_tenant_b_documents`
- `test_tenant_a_cannot_read_tenant_b_analysis`
- `test_tenant_a_cannot_list_tenant_b_analyses`
- `test_tenant_a_cannot_trigger_contextual_reanalysis_on_tenant_b_analysis`
- `test_tenant_a_cannot_attach_document_to_tenant_b_analysis`
- `test_organization_id_cannot_be_injected_via_store_endpoint`
- `test_unauthenticated_requests_are_rejected_on_all_key_endpoints`
- `test_tenant_a_cannot_trigger_ai_analysis_on_tenant_b_document`
- `test_ai_analysis_is_blocked_when_tenant_credits_are_exhausted`
- `test_ai_analysis_endpoint_requires_authentication`
- `test_error_responses_do_not_expose_credentials_or_env_secrets`
- `test_revoked_sanctum_token_is_immediately_rejected`
- `test_api_endpoints_enforce_rate_limiting`

### `tests/Feature/Security/Section2MultiTenantIsolationTest.php`

Ensures robust multi-tenant data isolation at the application and storage levels.

**Tests:**
- `test_client_cannot_inject_tenant_or_user_identity`
- `test_user_without_organization_is_denied`
- `test_document_uses_uuids_and_cannot_be_reassigned`
- `test_user_cannot_access_another_tenant_user_avatar`
- `test_tenant_a_cannot_read_tenant_b_document`
- `test_tenant_a_cannot_read_tenant_b_analysis`
- `test_tenant_a_cannot_view_web_analysis_of_tenant_b`
- `test_tenant_a_cannot_poll_tenant_b_document_status`
- `test_tenant_a_cannot_delete_tenant_b_document`
- `test_tenant_a_cannot_trigger_analysis_on_tenant_b_document`
- `test_tenant_a_cannot_attach_supporting_document_to_tenant_b_analysis`
- `test_tenant_a_cannot_list_tenant_b_supporting_documents`
- `test_tenant_a_document_query_strictly_filters_by_organization`
- `test_storage_path_enforces_tenant_partitioning`
- `test_eloquent_scope_for_organization_isolates_data`

### `tests/Feature/Security/Section3RolesAndAuthorizationTest.php`

Tests role-based access control (RBAC), preventing privilege escalation and unauthorized actions.

**Tests:**
- `test_document_permissions_matrix_across_roles`
- `test_analysis_permissions_matrix_across_roles`
- `test_user_management_permissions_matrix_across_roles`
- `test_billing_permissions_matrix_across_roles`
- `test_tenant_settings_permissions_matrix`
- `test_administrative_endpoints_restrict_non_admins`
- `test_horizontal_privilege_escalation_is_blocked`
- `test_vertical_privilege_escalation_is_blocked`
- `test_role_manipulation_via_profile_update_is_ignored`
- `test_is_admin_and_is_owner_field_manipulation_is_ignored`
- `test_tenant_id_manipulation_is_ignored_in_profile_and_actions`
- `test_direct_api_requests_to_admin_toggle_by_non_admin_are_blocked`
- `test_owner_cannot_be_deleted_by_regular_user_or_auditor`

### `tests/Feature/Security/Section4MassAssignmentTest.php`

Verifies protections against mass assignment vulnerabilities in models and API requests.

**Tests:**
- `test_all_models_have_explicit_fillable_properties`
- `test_strict_eloquent_prevents_unfillable_mass_assignment`
- `test_form_requests_strip_unwhitelisted_parameters`
- `test_dtos_prevent_unauthorized_field_hydration`
- `test_user_update_via_profile_cannot_override_organization_id`
- `test_force_fill_is_not_invoked_with_untrusted_client_data`
- `test_admin_endpoints_reject_unwhitelisted_privilege_injections`
- `test_document_upload_endpoint_strips_client_ownership_metadata`

### `tests/Feature/Security/Section5AuthenticationAndTokenTest.php`

Tests authentication security, password policies, rate limiting, and token management.

**Tests:**
- `test_login_locks_out_after_five_consecutive_failed_attempts`
- `test_password_spraying_rate_limiting_protects_multiple_accounts`
- `test_login_returns_identical_generic_error_for_existing_and_non_existing_users`
- `test_strong_password_policy_is_enforced_on_registration`
- `test_passwords_are_securely_hashed`
- `test_password_reset_tokens_are_secure_and_single_use`
- `test_password_reset_invalidates_all_active_tokens`
- `test_password_change_action_revokes_all_active_tokens`
- `test_individual_sanctum_token_revocation_works_immediately`

## Feature - User Preferences

### `tests/Feature/User/TutorialTest.php`

Tests the user onboarding tutorial visibility and dismissal functionality.

**Tests:**
- `test_unauthenticated_user_cannot_dismiss_tutorial`
- `test_authenticated_user_can_dismiss_tutorial`
- `test_tutorial_html_visibility_based_on_user_preference`
- `test_authenticated_user_can_dismiss_ancillary_tutorial`
- `test_ancillary_tutorial_html_visibility_based_on_user_preference`

## Unit - Actions

### `tests/Unit/Actions/Documents/AttachSupportingDocumentActionTest.php`

Tests the logic for attaching secondary documents to an analysis.

**Tests:**
- `test_attaches_supporting_document_successfully`
- `test_throws_exception_if_analysis_belongs_to_different_organization`
- `test_throws_exception_if_supporting_document_belongs_to_different_organization`
- `test_throws_exception_if_supporting_document_is_primary_document`
- `test_throws_exception_if_document_is_already_attached`

### `tests/Unit/Actions/Documents/StoreDocumentActionTest.php`

Tests the logic for securely storing new documents and handling storage failures.

**Tests:**
- `test_stores_document_successfully`
- `test_deletes_stored_file_and_logs_error_when_database_transaction_fails`

### `tests/Unit/Actions/User/DeleteAccountActionTest.php`

Tests the complete account deletion process including cleaning up associated data.

**Tests:**
- `test_deletes_user_organization_tokens_and_avatar`
- `test_deletes_user_without_organization`
- `test_deletes_user_without_avatar`
- `test_deletes_user_when_organization_is_soft_deleted`

### `tests/Unit/Actions/User/UpdateAvatarActionTest.php`

Tests the logic for uploading, updating, and removing user avatars.

**Tests:**
- `test_stores_avatar_and_updates_user_when_no_previous_avatar_exists`
- `test_deletes_old_avatar_when_updating_avatar`
- `test_throws_exception_when_file_storage_fails`

## Unit - General

### `tests/Unit/ExampleTest.php`

Basic unit test example.

**Tests:**
- `test_that_true_is_true`

## Unit - Services

### `tests/Unit/Services/AiCreditServiceTest.php`

Tests the credit system handling quotas, tier allowances, and deduction logic.

**Tests:**
- `test_provisions_free_tier_credits_with_weekly_expiry`
- `test_provisions_pro_tier_credits_with_monthly_expiry`
- `test_provisions_lifetime_tier_credits_as_one_time_pro`
- `test_free_users_can_only_use_lightweight_tier_models`
- `test_pro_users_can_use_standard_and_advanced_models`
- `test_calculates_credits_based_on_tokens_and_model_multiplier`
- `test_deducts_dynamic_token_based_credits`
- `test_allows_analysis_when_credits_positive_and_absorbs_overdraft`
- `test_weekly_quota_refreshes_when_period_expires`

### `tests/Unit/Services/Documents/DocumentStorageServiceTest.php`

Tests file storage abstractions, extension handling, and disk interactions.

**Tests:**
- `test_store_successfully_saves_file_and_returns_correct_metadata`
- `test_store_uses_custom_disk_and_base_path_from_config`
- `test_store_preserves_allowed_extension_case_insensitively`
- `test_store_falls_back_to_guessed_extension_when_extension_is_disallowed`
- `test_store_falls_back_to_bin_when_extension_disallowed_and_cannot_be_guessed`
- `test_store_throws_exception_when_storage_put_fails`
- `test_store_throws_exception_when_real_path_is_unavailable`
- `test_exists_returns_true_when_file_exists_and_false_otherwise`
- `test_delete_removes_file_from_disk`

### `tests/Unit/Services/LLM/Providers/RateLimitedAdapterTest.php`

Tests that external LLM provider API requests are properly rate-limited.

**Tests:**
- `test_provider_slug_returns_rate_limited`
- `test_complete_hits_rate_limiter_and_returns_result_on_success`
- `test_complete_adds_extra_rate_limiter_hit_when_upstream_returns_rate_limit_error`
- `test_complete_does_not_add_extra_hit_for_non_rate_limit_exception`
- `test_complete_does_not_hit_rate_limiter_when_rate_limit_rpm_is_zero`
- `test_enforces_local_rate_limit_pre_request`

### `tests/Unit/Services/LlmPromptBuilderTest.php`

Verifies that LLM prompts are correctly constructed using provided context and documents.

**Tests:**
- `test_build_system_prompt_returns_context_and_instructions`
- `test_build_user_prompt_includes_document_context_and_content`
- `test_build_contextual_user_prompt_includes_prior_analysis_and_supporting_docs`

### `tests/Unit/Services/NvidiaNimAnalysisServiceTest.php`

Tests integration with the NVIDIA NIM API for document analysis and rate limits.

**Tests:**
- `test_throws_exception_if_api_key_is_missing`
- `test_analyze_executes_successfully_and_returns_structured_result`
- `test_handles_429_too_many_requests_rate_limit_error`
- `test_enforces_local_40_rpm_rate_limit`

### `tests/Unit/Services/System/ExternalServiceHealthServiceTest.php`

Tests health check probes against external dependencies like Groq, NVIDIA, and Unstructured APIs.

**Tests:**
- `test_groq_returns_unconfigured_when_api_key_is_missing`
- `test_groq_returns_disconnected_on_http_failure`
- `test_groq_returns_connected_when_model_is_available`
- `test_groq_returns_connected_warning_when_model_not_in_list`
- `test_groq_handles_exception_during_request`
- `test_nvidia_nim_returns_unconfigured_when_api_key_is_missing`
- `test_nvidia_nim_returns_disconnected_on_http_failure`
- `test_nvidia_nim_returns_connected_when_model_is_available`
- `test_nvidia_nim_returns_connected_warning_when_model_not_in_list`
- `test_nvidia_nim_handles_exception_during_request`
- `test_unstructured_returns_unconfigured_when_url_is_missing`
- `test_unstructured_returns_unconfigured_when_key_is_missing`
- `test_unstructured_returns_disconnected_on_401_or_403`
- `test_unstructured_returns_disconnected_on_500_server_error`
- `test_unstructured_returns_connected_on_successful_probe`
- `test_unstructured_handles_legacy_general_url`
- `test_unstructured_handles_exception_during_request`
- `test_check_all_aggregates_all_service_checks`
