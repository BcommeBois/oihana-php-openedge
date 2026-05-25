# Locking hints

A **locking hint** is an explicit indication given to OpenEdge about the locking strategy to apply for a query. It's a keyword added after a table reference in `FROM` or as a `WITH(...)` sub-clause. Mainly used to unblock reads on a production database that takes lots of locks.

The [`LockingHint`](../../../src/oihana/openedge/db/enums/LockingHint.php) enum lists the five hints the framework supports.

> **Canonical reference.** [Progress SQL — Locking hints (READPAST)](https://docs.progress.com/bundle/openedge-sql-development-117/page/The-READPAST-locking-hint.html).

## Why this matters

On a production Progress ERP, long ABL transactions are common (monthly accounting close, stock calculation, invoice generation). Each takes locks on the tables it touches. An external SQL query trying to read **the same table** at the same time will wait — or even fail on timeout.

For reporting / HTTP catalog use, you accept reading slightly stale data (two seconds late) rather than blocking production. That's the primary use case for locking hints.

## The five hints

```php
use oihana\openedge\db\enums\LockingHint ;
```

| Constant | SQL | Effect |
|---|---|---|
| `LockingHint::NOLOCK` | `NOLOCK` | Reads without locking; sees uncommitted data (*dirty read*). |
| `LockingHint::READPAST` | `READPAST` | Skips rows locked by another transaction instead of waiting. |
| `LockingHint::NOWAIT` | `NOWAIT` | Immediately throws an error if the query must wait for a lock. |
| `LockingHint::WAIT` | `WAIT` | Standard wait (default behaviour, explicit). |
| `LockingHint::WITH_NOLOCK` | `WITH (NOLOCK)` | SQL Server-style table-level syntactic variant of `NOLOCK`. |

## `NOLOCK` — the most-used

Reads without taking locks, and **sees uncommitted data** from other transactions. This is the hint to use for reporting that must **never** block production.

### Raw SQL

```sql
SELECT *
FROM   PUB.commandes_commandes c WITH (NOLOCK)
WHERE  c.dat_crt > '2026-01-01'
```

### In the framework

```php
use oihana\openedge\db\enums\LockingHint ;
use oihana\openedge\enums\OpenEdge as SQL ;

SQL::LOCKING_HINT => LockingHint::WITH_NOLOCK ,
```

The builder injects the hint after `FROM`.

### Trade-off

| Pro | Con |
|---|---|
| No locks taken → never waits, never deadlocks | Dirty read — may see a row that's later rolled back |
| Maximum performance for catalog queries | Inappropriate for critical queries (accounting balance, etc.) |
| No impact on production | On a table updated heavily, aggregate stats can be skewed |

**Rule of thumb:** `NOLOCK` is acceptable for 95 % of catalog HTTP reads. For the remaining 5 % (accounting totals, stock state at instant T), you need standard `READ COMMITTED` or an application-level snapshot.

## `READPAST` — a safer alternative

Skips locked rows **without seeing them**. Safer than `NOLOCK` (no dirty read), but the result is partial: rows currently being written by another transaction are missing from the result.

```php
SQL::LOCKING_HINT => LockingHint::READPAST ,
```

Use case: a dashboard that aggregates counters and tolerates missing a few rather than seeing wrong intermediate values.

## `NOWAIT` — fail-fast

Throws an SQL error immediately if the query must wait for a lock. Useful in a CLI context where you prefer to fail quickly and retry later, rather than block for an hour.

```php
SQL::LOCKING_HINT => LockingHint::NOWAIT ,
```

## Choosing a locking hint

| Scenario | Recommended hint |
|---|---|
| HTTP catalog read, reporting, dashboards | `WITH (NOLOCK)` |
| Aggregated counters tolerating partial data | `READPAST` |
| Periodic synchronisation (harvest) that mustn't block | `WITH (NOLOCK)` |
| Critical calculation that must see consistent data | no hint (`READ COMMITTED` standard) |
| Ad-hoc CLI check that must fail if locked | `NOWAIT` |

## Common mistakes

### Putting `NOLOCK` everywhere by habit

Strong temptation after one timeout incident. But on aggregating reads (`SUM`, `COUNT`), `NOLOCK` can produce wrong results due to in-flight uncommitted writes. **Only use when you explicitly accept the dirty read.**

### Combining `NOLOCK` with an `INSERT/UPDATE`

On an `INSERT ... SELECT ...` or `UPDATE ... FROM ... WHERE ...`, a `NOLOCK` on the `SELECT` side can lead to mutating based on dirty data. Source of subtle, hard-to-reproduce bugs. **Avoid** on write paths.

> The framework's HTTP controller being read-only by doctrine ([introduction.md](../introduction.md#a-doctrine-openedge-is-read-only-over-http)), this trap doesn't apply via HTTP. It can apply on the CLI side.

## See also

- [Progress outer join](outer-join.md) — another `WHERE` clause specificity.
- [Connection timeouts](timeouts.md) — model's `connectTimeout` and `serverTimeout`, another lever to handle waiting.
- [SQL clauses](../sql/sql-clauses.md) — `LookingHintTrait` of the query builder.
- [Progress SQL — READPAST](https://docs.progress.com/bundle/openedge-sql-development-117/page/The-READPAST-locking-hint.html) — canonical reference.
