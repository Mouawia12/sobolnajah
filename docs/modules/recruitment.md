# Recruitment Module

## Domain Model
- `JobPost`
- `JobApplication`

## Main Flows
- Admin creates and manages job posts (draft/published/closed).
- Public candidates submit applications with CV uploads.
- Admin reviews applications and updates status.

## Permissions
- Admin-only job post and application management endpoints.
- Public apply endpoint is allowed only for published posts.
- Policy scope enforces same-school access for CV/status operations.
