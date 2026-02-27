# Sobol Najah Architecture

## Overview
Sobol Najah is a Laravel monolith structured by business domains with strict school-level isolation and policy-based authorization.

## Runtime Layers
- Presentation: Blade views + route endpoints in `routes/web.php`.
- HTTP/Application: Controllers + FormRequests in `app/Http/*`.
- Domain/Application Services: Actions/Services in `app/Actions/*`, `app/Services/*`.
- Data: Eloquent models in `app/Models/*` backed by MySQL migrations.

## Core Principles
- Multi-school isolation: school-scoped queries are required for school-bound admins.
- Security-first writes: all state-changing endpoints use FormRequest + authorization checks.
- Controller thinness: business logic is moved into Actions/Services for critical flows.
- Performance baseline: admin-heavy lists use server-side pagination/search/filters.

## Domain Map
- Core Academic: students, teachers, inscriptions, schools, sections, classrooms, promotions.
- Content: publications, agendas, grades, exams, notes, absences.
- Communication: notifications, chat rooms/messages.
- Recruitment: job posts and applications.
- Accounting: contracts, installments, payments, receipts.

## Shared Cross-Cutting Concerns
- AuthN/AuthZ: Laravel auth + role checks + model policies (`app/Policies/*`).
- Validation: dedicated FormRequests for write operations.
- Caching: lookup/dashboard/public-reference caches with explicit invalidation.
- File security: private/local storage and controlled file download endpoints.
- Observability: test-first for security/performance-sensitive paths.
