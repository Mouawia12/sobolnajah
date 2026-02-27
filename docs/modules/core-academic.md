# Core Academic Module

## Domain Model
- `School`, `Schoolgrade`, `Classroom`, `Section`
- `StudentInfo`, `Teacher`, `Inscription`, `Promotion`

## Main Flows
- Admission pipeline: inscription creation, approval/rejection, and conversion to student enrollment.
- Academic structure management: school -> grade -> classroom -> section CRUD.
- Promotion/graduation lifecycle across sections and school years.

## Permissions
- Admin role required for academic management endpoints.
- Policy checks on entity-level access (student/teacher/inscription/promotion/school entities).
- School-bound admins are restricted to their `school_id` scope.
