# Feature Architecture

Status: Active foundation for incremental clean architecture.

This folder keeps feature-oriented application code while Eloquent models remain in `app/Models`.

## Current Rules

- Controllers stay in `app/Http/Controllers` and only orchestrate requests, actions, and responses.
- Multi-step writes live in `Features/<Feature>/Actions`.
- Important validation lives in `Features/<Feature>/Http/Requests`.
- Read/list/filter composition lives in `Features/<Feature>/Queries`.
- Shared small utilities live in `Features/Shared`.
- Do not add generic repositories unless a real module needs a persistence abstraction.

## Current Implemented Feature Layers

- `Auth`: member registration, Google member resolution, complete profile workflow.
- `PublicWebsite`: public settings, home data, public class/product filters, services/about/gallery data.
- `Shared`: Indonesian phone normalization.

Future modules should follow this shape before adding heavy business logic for admin, member portal, payment, booking, QR, export, or AI.
