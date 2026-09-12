# User Stories and Use Cases

This document details the official user stories and use cases representing the current functionality of the application. It is organized by feature modules.

## 1. Authentication & Onboarding
* **As a Visitor**, I want to view a landing page, so that I can understand what the application does.
* **As a Visitor**, I want to view the terms and conditions, so that I can understand the legal agreements of using the app.
* **As a Visitor**, I want to be able to register for a new account, so that I can start using the application's features.
* **As a User**, I want to be able to log into my account, so that I can access my personalized dashboard and documents.
* **As a User**, I want to be able to log out of my account, so that I can securely end my session.
* **As a User**, I want to be able to dismiss the onboarding tutorial, so that it no longer appears once I understand how to use the app.

## 2. Dashboard & Document Management
* **As a User**, I want to view a dashboard with my key metrics and recent documents, so that I have a quick overview of my account's activity.
* **As a User**, I want to select a specific AI Model (e.g., Cerebras AI, Llama, etc.) from the available options, so that my documents are processed using the model of my choice.
* **As a User**, I want to switch my LLM provider (Groq, NVIDIA NIM, Cerebras, Qwen Cloud), so that I can control which underlying service powers the AI models.
* **As a User**, I want to upload documents (PDF, DOCX) up to 50 MB, so that they can be processed and analyzed by the system.
* **As a User**, I want to see the real-time processing status of my uploaded documents, so that I know when the analysis is complete.
* **As a User**, I want to delete a document I have uploaded, so that I can remove it and all its associated analysis data permanently from the system.

## 3. Document Analysis
* **As a User**, I want to view a detailed analysis of a processed document, so that I can read the AI-generated insights and summaries.
* **As a User**, I want to view a clause-by-clause analysis of a processed document, so that I can thoroughly examine the specific contractual or legal elements identified by the AI.
* **As a User**, I want to view a complementary analysis of ancillary documents, so that I can add it to my previous analysis.

## 4. Account Settings
* **As a User**, I want to view my account settings page, so that I can manage my profile and preferences.
* **As a User**, I want to change my profile's name, so that my account reflects my current identity.
* **As a User**, I want to upload and change my profile avatar, so that I can personalize my account visually.
* **As a User**, I want to change my password securely by providing my current password, so that I can keep my account safe.
* **As a User**, I want to permanently delete my account and all associated data, so that I can remove my information completely if I decide to stop using the application.

## 5. Administration & System Configuration
* **As an Administrator**, I want to manage AI models and their corresponding tiers, so that I can control which models are available to different user plans.
* **As an Administrator**, I want to configure the credit multipliers for different AI models, so that the credit consumption accurately reflects the computing costs.
* **As an Administrator**, I want to view and manage subscription plans, so that I can adjust the limits (documents, storage, AI credits) and pricing based on business requirements.
* **As an Administrator**, I want to monitor the system's external services (e.g., Stripe, LLM Providers), so that I can ensure all integrations are functioning correctly.
* **As an Administrator**, I want to view an organization's active plan and credit usage, so that I can assist with support inquiries and billing.
