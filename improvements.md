# Code Improvements

## High Priority (Runtime Risks)

### ~~1. Division by zero in LeaderBoardService~~ FIXED
**File:** `src/Service/LeaderBoardService.php:65-69`
Added guard clause to skip `Fields/Links` entry when connector is 0 or null.
Test: `tests/Service/LeaderBoardServiceTest.php`

### ~~2. Unsafe getenv() in TelegramBotHelper~~ NOT A BUG
**File:** `src/Service/TelegramBotHelper.php:59,72`
Already guarded with `if (!$allowedIdString) { return false; }` before `explode()`.

### ~~3. Missing exception handling in GoogleIdentityAuthenticator~~ FIXED
**File:** `src/Security/GoogleIdentityAuthenticator.php:54-59`
Wrapped `verifyIdToken()` in try/catch, re-throws as `AuthenticationException`.
Test: `tests/Security/GoogleIdentityAuthenticatorTest.php`

### ~~4. AppCustomAuthenticator blocks production~~ NOT A BUG
**File:** `src/Security/AppCustomAuthenticator.php:43-44`
Intentional by design — this is a dev/test-only form login authenticator. Production uses Google OAuth via `GoogleIdentityAuthenticator`.

---

## Medium Priority (Performance & Architecture)

### ~~5. N+1 queries in LeaderBoardService~~ FIXED
**File:** `src/Repository/AgentStatRepository.php:123-139`
`getAgentLatest()` was loading ALL stats for an agent to get first/last. Now uses `setMaxResults(1)` with correct sort order.

### 6. findAll() without limits — LOW RISK
**Files:** `DefaultController`, `StatsController`, `EventController`
Loads entire tables into memory. Acceptable for a small community app with <100 agents.

### ~~7. Static variable caching in EventHelper~~ FIXED
**File:** `src/Service/EventHelper.php`
Replaced `static` method variables with instance properties (`$eventsBySpan`, `$challengesBySpan`). No longer persists across CLI/queue requests.

### ~~8. Complex controller logic in StatsController~~ FIXED
**File:** `src/Controller/StatsController.php`
Extracted 45+ lines of medal tracking logic to `MedalChecker::getMedalsGained()`. Controller now calls the service method.
Test: `tests/Service/MedalCheckerGetMedalsGainedTest.php`

### ~~9. Entity mutations for display in DefaultController~~ FIXED
**File:** `src/Controller/DefaultController.php`
Removed duplicated event categorization loop. Now delegates to `EventHelper::getEventsInSpan()` which already handles timezone conversion. Removed unused `EventRepository` dependency.

---

## Code Quality

### ~~10. Exception swallowing in MailerHelper~~ FIXED
**File:** `src/Service/MailerHelper.php`
Injected `LoggerInterface` and added `$this->logger->error()` in all catch blocks. Errors are now logged while still returning the message string for UI display.
Test: `tests/Service/MailerHelperTest.php` (added `testFailureLogsError`)

### ~~11. Swallowed exceptions in production in StatsController~~ FIXED
**File:** `src/Controller/StatsController.php`
Added `LoggerInterface` to constructor and `$this->logger->error()` in the catch block. Errors are now logged in all environments, not just dev.

### ~~12. Decimal columns typed as string in Agent~~ NOT A BUG
**File:** `src/Entity/Agent.php:30-34`
Doctrine intentionally maps `decimal` columns to PHP `string` to preserve precision. Callers cast to `(float)` when needed. This is correct behavior.

### ~~13. Incomplete ArrayAccess in AgentStat~~ FIXED
**File:** `src/Entity/AgentStat.php`
`offsetSet()` and `offsetUnset()` now throw `BadMethodCallException` instead of being silent no-ops. The entity is read-only via ArrayAccess.
Test: `tests/Entity/AgentStatComputeTest.php` (added `testOffsetSetThrows`, `testOffsetUnsetThrows`)

### 14. Missing entity validation constraints
Entities like `Agent`, `User` lack `#[Assert\*]` attributes (e.g., `NotBlank`, `Email`, `Length`). This is a larger task that could affect forms and data flow — best addressed incrementally.

---

## Test Coverage Gaps

- **Controllers**: 26 classes, ~5 have tests — critical paths like stats upload and login need integration tests
- **Repositories**: 0 tests for custom query methods
- **TelegramBotHelper**: Untested despite handling authorization logic
- **FileUploader**: Security-sensitive, untested
