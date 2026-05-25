# Connection timeouts

Three levers let you control how long an OpenEdge query can take before being abandoned: a **DSN** parameter at connection time, and two **model** methods exposed by `OpenEdgeHelperTrait`.

| Lever | Level | When to set it |
|---|---|---|
| `queryTimeout` (DSN) | All queries on the connection | At boot, in the TOML config. |
| `connectTimeout()` (model) | All queries on the current connection handle | At each OpenEdge session open, from the model. |
| `serverTimeout()` (model) | All queries on the server for this connection | At each open, same as `connectTimeout()` but server-side scope. |

## `queryTimeout` on the DSN

The DSN `queryTimeout` parameter ([dsn.md](../dsn.md#querytimeout)) sets the default timeout of **all queries** going through this PDO. In seconds; three special values:

| Value | Behaviour |
|---|---|
| `-1` | No timeout. The driver also ignores `SQL_ATTR_QUERY_TIMEOUT` on the ODBC side. For long CLI harvests. |
| `0` | No default timeout, but the driver respects a `SQL_ATTR_QUERY_TIMEOUT` set through another channel. |
| `x > 0` | All queries time out after `x` seconds. |

Recommended settings:

```toml
[odbc]
# HTTP exposure: 5 minutes max to avoid saturating the API
queryTimeout = 300

# CLI / harvest: no timeout
# queryTimeout = -1
```

> This parameter is fixed at PDO connection open — not modifiable at runtime. To change the timeout during a session, use `connectTimeout()` or `serverTimeout()` below.

## `connectTimeout()` and `serverTimeout()` on the model

`OpenEdgeHelperTrait` ([OpenEdgeHelperTrait.php](../../../src/oihana/openedge/models/traits/OpenEdgeHelperTrait.php)) — used by the [`Documents`](../models.md) model — exposes two methods to adjust timeouts **after** the connection has opened.

### `connectTimeout(int $delay)`

Max query execution delay, viewed connection-side (client).

```php
$customers->connectTimeout( 30 ) ; // 30 seconds
```

On the SQL side, equivalent to:

```sql
SET PRO_CONNECT QUERY_TIMEOUT :delay
```

The setting applies to **all subsequent queries** on the same PDO connection. Persistence: until the PDO is closed.

### `serverTimeout(int $delay)`

Max query execution delay, **viewed server-side** Progress. Similar to `connectTimeout` but the overflow is computed server-side, which is more accurate when the network is slow.

```php
$customers->serverTimeout( 60 ) ;
```

On the SQL side:

```sql
SET PRO_SERVER QUERY_TIMEOUT :delay
```

### `connectTimeout` vs `serverTimeout` — which to choose

| Case | Prefer |
|---|---|
| Bound the total duration seen by the client (PHP) | `connectTimeout` |
| Bound the *actual* server execution duration (useful on a slow network where round-trips matter) | `serverTimeout` |
| You want both | The two aren't exclusive — combining both is legitimate. |

In 90 % of cases, the DSN's `queryTimeout` suffices. Both trait methods are useful for two scenarios:

1. **A harvest CLI that changes strategy mid-execution.** At the start of the harvest you disable the timeout (`-1`), then restore it to 60 s at the end for verification queries.
2. **An HTTP session that wants a tighter timeout** on a specific route known to be fast (200 ms expected, 5 s max), without touching the global timeout.

## `updateStatistics(string $table)`

`OpenEdgeHelperTrait` also exposes a method `updateStatistics()` that asks the Progress server to **recompute the statistics** of a table and its indexes. These statistics are consumed by the Progress SQL optimiser to pick a query plan.

```php
$customers->updateStatistics( 'PUB.clients_clients' ) ;
```

On the SQL side:

```sql
UPDATE TABLE STATISTICS AND INDEX STATISTICS AND ALL COLUMN STATISTICS FOR PUB.clients_clients
```

### When to call it

- **After a massive harvest** (insertion of hundreds of thousands of rows) — statistics are stale and the optimiser may pick a catastrophic plan.
- **After creating an index** — Progress doesn't auto-update statistics on index creation.
- **Never under live traffic** — it's a heavy operation that takes locks during the computation. Restrict to maintenance windows.

> This method is documented but rarely used in host applications. It's exposed for consistency with the Progress ecosystem, and useful in manual migration / seed scripts.

## See also

- [ODBC connection and multi-database](../connection.md) — TOML `[odbc]` and `[databases.*]` configuration.
- [ODBC DSN in detail](../dsn.md#querytimeout) — detail of the `queryTimeout` parameter.
- [`Documents` model](../models.md) — how the model consumes `OpenEdgeHelperTrait`.
- [Progress SQL — PRO_CONNECT and PRO_SERVER](https://docs.progress.com/bundle/openedge-sql-reference/page/SET-statement.html) — canonical reference.
