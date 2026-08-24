# Architecture

## Style

Modular monolith with explicit module ownership.

## Proposed modules

- Identity
- Catalog
- Discovery
- Collections
- Accounts
- Integrations
- Editorial
- Moderation
- SEO
- Analytics
- System

## Dependency direction

UI -> Application -> Domain -> Infrastructure

Modules communicate through:
- public application services;
- immutable DTOs;
- domain/application events for secondary effects.

They must not reach into another module's internal models or tables casually.

## Request flow

HTTP request -> Route -> Controller/Livewire -> Form Request/Policy ->
Application Action -> Domain rules -> Repository/Eloquent -> Event/Job ->
Response/View Resource

## Async work

Queue:
- provider imports;
- metadata refresh;
- artwork processing;
- sitemap generation;
- search indexing;
- notifications;
- analytics aggregation.

Every external job must be idempotent and retry-safe.

## Caching

Cache derived responses, never business truth.
Keys require versioning and bounded TTL.
Invalidation happens from domain/application events.

## Architecture test

New dependencies must answer:
- Which module owns this?
- Is it business truth or a projection?
- Can provider failure break the page?
- Is the operation idempotent?
- What is the fallback?
