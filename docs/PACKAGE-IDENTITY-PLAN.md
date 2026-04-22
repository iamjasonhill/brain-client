# Package Identity Plan

This note records the current package naming decision for `brain-client` and
the compatibility constraints that should govern any future rename.

## Current Recommendation

Keep the published Composer package name as `brain-nucleus/client` for now.

This is a compatibility hold, not a branding endorsement.

Reasons:

- downstream installs already reference `brain-nucleus/client`
- Composer package renames are disruptive because consumers update by package
  name, not just by repository URL
- the wider control-plane rename is still settling, so renaming this package
  first would create avoidable ambiguity between package identity and
  application identity

## Boundary Clarification

- `brain-client` is the canonical standalone source for the reusable client and
  package layer
- `TheBrain` is the control-plane application/runtime and is moving toward the
  `MM-Control-Plane` identity
- the embedded `brain-client` copy inside `TheBrain` should be treated as a
  transitional runtime mirror, not the package source of truth

## Compatibility Risks If The Package Name Changes

Changing the Composer package name later would affect:

- install commands in downstream apps
- existing `composer.json` manifests and any private setup docs that pin
  `brain-nucleus/client`
- update flows that currently rely on `composer update brain-nucleus/client`
- any release notes, support scripts, or onboarding instructions that reference
  the current package name

Likely migration risks:

- installs can fail if downstream projects switch names before the new package
  is published and documented
- teams can accidentally track the wrong package if old and new names coexist
  without a staged cutover
- embedded-copy cleanup inside `TheBrain` becomes harder if package naming and
  control-plane naming both change at once

## Required Staging For Any Future Rename

Do not rename the package until the control-plane repo rename and embedded-copy
normalization path are clear.

If a rename is approved later, stage it as an explicit migration:

1. decide the final package name and owner/repository mapping
2. publish a compatibility note and upgrade guide before changing install docs
3. update this repo's install, versioning, and integration docs together
4. keep the standalone `brain-client` repo as the canonical package source
   throughout the transition
5. verify how downstream projects will move, including any repos still using
   the embedded copy inside `TheBrain`

## Immediate Repo-Side Follow-Through

For now this repo should:

- keep docs explicit that `brain-client` is the canonical package source
- keep `brain-nucleus/client` as the documented install target for stability
- treat package renaming as a tracked future decision rather than silent drift
