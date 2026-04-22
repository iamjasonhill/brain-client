# OVERSEER

## Current Controller

`brain-client` is the canonical reusable client/package layer for the Brain
control-plane ecosystem.

It exists to provide:

- Laravel package integration for event delivery
- standalone PHP event client support
- shared event/specification documentation
- web tracking assets used by connected sites

It is distinct from:

- `MM BRAIN`, which owns strategy, priority, interpretation, and history
- `Brain2026`, which owns the SEO, research, builder, and orchestration lane
- `TheBrain`, which is the active control-plane application and preferred
  future identity `MM-Control-Plane`

## Current Boundary

`brain-client` should remain focused on:

- reusable client code and package distribution
- integration setup guidance for connected projects
- event and capability contract material shared across client implementations
- standalone/static integration assets that belong with the package

It should not drift into:

- strategy or cross-portfolio memory owned by `MM BRAIN`
- SEO/builder orchestration owned by `Brain2026`
- control-plane application/runtime ownership owned by `TheBrain`

## Naming Rule

Only `MM BRAIN` should retain the true `Brain` identity at the system level.

For this package, Bossman and repo-side read is:

- the standalone repo is canonical and should stay active
- the package name `brain-nucleus/client` can remain temporarily for
  compatibility
- any future package renaming should be staged only after the control-plane
  rename path is clearer and downstream install/versioning impact is mapped

## Repo Readiness Notes

This repo already functioned as a real standalone package, but it needed a
clear repo-side reporting surface and a cleaner statement of source-of-truth,
because some docs still pointed people back to `thebrain` as if that were the
canonical package source.

Current fix:

- `OVERSEER.md` now exists as the repo-side reporting surface Bossman expects
- root docs now clarify that this repo is the canonical package source
- install and reference docs now point to the standalone `brain-client` repo
  instead of the embedded copy in `TheBrain`

## Change Log

### 2026-04-23 04:46:07 AEST

- Issue / trigger:
  Bossman issue #1, staged package naming and downstream compatibility review
- What changed:
  - added `docs/PACKAGE-IDENTITY-PLAN.md` to document the package naming
    recommendation, downstream risks, and staged rename conditions
  - linked the package identity plan from `README.md`, `INDEX.md`, and
    `VERSIONING.md` so the decision is visible from the main package docs
- What was fixed:
  - the repo now has an explicit package-side recommendation instead of leaving
    `brain-nucleus/client` as an accidental naming hold
  - compatibility and release constraints are now documented in-repo for quick
    Bossman review
- What remains:
  - keep `brain-nucleus/client` in place until the control-plane rename path
    and embedded-copy normalization in `TheBrain` are clearer
  - if a future rename is approved, stage it as a documented downstream
    migration rather than a blind package rename
- Package naming recommendation:
  keep `brain-nucleus/client` for now as a compatibility hold, not as a signal
  that the wider Brain naming is fully settled
- Compatibility / versioning constraints:
  - downstream installs and update flows currently depend on
    `brain-nucleus/client`
  - install/versioning docs must move together if a future rename is approved
  - the standalone `brain-client` repo should remain the canonical package
    source throughout any later transition

### 2026-04-22 13:22:00 AEST

- Issue / trigger:
  Bossman package-side cleanup pass after clarifying `TheBrain` as the
  control-plane app and `brain-client` as the canonical reusable package
- What changed:
  - created root `OVERSEER.md`
  - clarified that this repo is the canonical standalone package source
  - updated install/reference docs so they point to `brain-client` instead of
    treating `thebrain` as the package source of truth
  - recorded that package renaming should remain staged rather than immediate
- What was clarified about boundaries:
  - this repo remains the reusable client/package layer
  - it should not be treated as the control-plane app itself
  - the embedded copy inside `TheBrain` is transitional, not canonical
- What remains:
  - decide whether and how package naming should evolve after the control-plane
    rename path is settled
  - keep the standalone repo as the canonical source while `TheBrain` works
    through its embedded-copy normalization
