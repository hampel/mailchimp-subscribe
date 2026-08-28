# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## This package is abandoned

`hampel/mailchimp-subscribe` wrapped the MailChimp API v3 for one job — subscribing,
unsubscribing and cleaning addresses on a single mailing list. It was retired in August 2026.
**Use [`drewm/mailchimp-api`](https://packagist.org/packages/drewm/mailchimp-api) instead**; it is
the same shape, actively maintained, and Mailchimp's own `mailchimp/marketing` SDK is the other
option.

Three findings settled it. Nobody used it — 47 Packagist downloads in ten years, none in the last
twelve months, no dependents, and no consumer of ours outside retired XenForo 1 `library/` trees.
It could not be installed beside anything current, because `guzzlehttp/guzzle ~6.0` pins a cycle
that reached end of life on 2023-10-31. And it never worked on the Guzzle version it claimed: the
1.0.0 rewrite left Guzzle 5 references behind, so the parse `catch` in `MailChimp::send()` names a
class Guzzle 6 does not have, `MailChimpException` imports a namespace it deleted, and the
empty-API-key path throws an unimported `RuntimeException` that resolves to nothing. Every error
path is broken, which is the whole reason a wrapper like this existed.

## If you are here to change something

Almost certainly the change belongs somewhere else. Whatever needs MailChimp should call
`drewm/mailchimp-api` from the consuming application; this package should not gain a Guzzle 7
constraint, a test suite, or a release. **Do not run the `package-audit` skill against it** — that
audit has been run, and abandoning it was the outcome.

The exception is the abandonment itself being incomplete: the Packagist **Abandon** flag is set
through the web UI, not from `composer.json`, and it is what makes Composer warn on install. If
that has not been done, it is the only outstanding work here.

The reasoning behind all of this, in full, is in the commit message on `1cd2b01`. The code still
reads fine if you ever need to reimplement a behaviour elsewhere — start at `src/MailingList.php`,
which is where the API semantics live.
