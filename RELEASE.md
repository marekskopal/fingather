# Release process

fingather follows [Semantic Versioning](https://semver.org/). Releases are tagged on the `public` branch.

## Versioning rules

- **MAJOR** — breaking API or DB-schema changes.
- **MINOR** — new user-visible features.
- **PATCH** — bug fixes and minor changes.

iOS keeps its own `ios-v*` track, independent of the web app.

## Release steps

1. On `public`, decide the next version per the rules above.
2. Move entries from `## [Unreleased]` under a new `## [X.Y.Z] - YYYY-MM-DD` heading in `CHANGELOG.md`.
3. Bump `frontend/package.json` `"version"` to `X.Y.Z`.
4. Commit: `Release vX.Y.Z`.
5. Annotated tag: `git tag -a vX.Y.Z -m "Release X.Y.Z"`.
6. Push branch and tag to the open source remote:
   ```
   git push public public:main
   git push public vX.Y.Z
   ```
7. After the next `public → main` merge, also push the tag to the SaaS remote:
   ```
   git push origin vX.Y.Z
   ```
8. On GitHub (`marekskopal/fingather`), create a Release from the tag and paste the changelog section as the body.
