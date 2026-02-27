# Content Module

## Domain Model
- `Publication`, `Gallery`, `Agenda`, `Grade`
- `Exames`, `NoteStudent`, `Absence`

## Main Flows
- Publication management with media upload and signed media delivery.
- Exam and notes upload/download pipeline using private storage.
- Absence tracking per student/date with section-based filtering.
- Public content rendering for selected resources with scoped datasets.

## Permissions
- Admin-only writes for content management.
- Policy-based access for view/update/delete on sensitive resources.
- School scoping for admin datasets and file access.
