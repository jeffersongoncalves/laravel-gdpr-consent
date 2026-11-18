# Changelog

All notable changes to this project will be documented in this file.

## 2.0.0 - 2026-11-18

### Breaking

- The engine moves to [laravel-privacy](https://github.com/jeffersongoncalves/laravel-privacy), the base shared by the LGPD, GDPR, CCPA, US privacy and Quebec suites. Every class of this package keeps its name and now extends its `JeffersonGoncalves\Privacy` base. If you extend one of them, check your overrides.

### Unchanged

- Config keys, tables, migrations, commands, events and the `#[PersonalData]` declarations: apps need no change.

### Added

- Suites installed side by side (e.g. LGPD and GDPR) only see their own declarations and events.

## 1.0.0 - 2026-11-17

First release: GDPR consent ledger for Laravel, part of the laravel-gdpr suite.

- Append-only records per purpose and terms version, with IP address, user agent, source and metadata as proof (Art. 7(1)).
- Grant and withdraw with the same call shape: withdrawing is as easy as giving consent (Art. 7(3)).
- Bumping a purpose's version invalidates older consents. Consents can expire after a configured period.
- Users (any Eloquent model) and anonymous visitors (a random id in an encrypted cookie). When a visitor logs in, their latest decisions are copied to the account.
- Optional POST endpoint for cookie banners, and the `ConsentRecorded` event.

## [Unreleased]
