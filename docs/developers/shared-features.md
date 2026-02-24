# Shared Features

SmartLink Manager uses the following shared libraries and features.

## `lindemannrock/base`

| Feature | Description |
|---------|-------------|
| `PluginHelper::bootstrap()` | Initializes base module, Twig globals, and logging configuration |
| `PluginHelper::applyPluginNameFromConfig()` | Overrides plugin name from config file |
| `SettingsConfigTrait` | Config file override detection and log level validation |
| `SettingsDisplayNameTrait` | Standardized plugin name helper methods |
| `SettingsPersistenceTrait` | Database persistence for Settings models |
| `GeoHelper` | Geographic utilities (country code to name conversion) |
| `GeoLookupTrait` | IP-to-location lookup shared by `AnalyticsService` and `AnalyticsTrackingService` |
| `DeviceDetectionTrait` | User-agent parsing and device/platform detection used by `DeviceDetectionService` |
| `DateFormatHelper` | DB-agnostic timezone-aware SQL expressions (`localDateExpression()`, `localHourExpression()`) |
| `DbHelper` | DB-agnostic JSON extraction (`jsonExtract()`) used across analytics queries |
| `DateRangeHelper` | Date range query helpers (`applyToQuery()`) used by `AnalyticsService` |
| `ExportHelper` | CSV, JSON, and Excel export formatting used by `AnalyticsController` |
| `CpNavHelper` | Subnav building and first-accessible-route utilities used by `SmartLinkManager` and `SmartlinksController` |

### Details

**PluginHelper::bootstrap()**

Provides plugin name helpers in Twig templates (see Twig Globals section)

**PluginHelper::applyPluginNameFromConfig()**

Allows customizing the plugin display name via config/{plugin-handle}.php

**SettingsConfigTrait**

Settings can be overridden via config/{plugin-handle}.php. Debug logging requires devMode.

**SettingsDisplayNameTrait**

Provides getDisplayName(), getFullName(), getPluralDisplayName(), etc.

**SettingsPersistenceTrait**

Settings are stored in database with automatic type conversion for boolean, integer, float, and JSON fields.

**GeoHelper**

ISO 3166-1 alpha-2 country code utilities

**GeoLookupTrait**

Shared geo lookup logic used by analytics services to resolve IP addresses to country/city via the configured provider.

**DeviceDetectionTrait**

User-agent parsing logic shared with DeviceDetectionService. Handles platform detection, bot detection, and device caching.

**DateFormatHelper**

Provides `localDateExpression()` and `localHourExpression()` — DB-agnostic SQL expressions that apply the Craft timezone offset. Used by analytics chart and summary services.

**DbHelper**

Provides `jsonExtract()` for DB-agnostic JSON field queries. Used throughout analytics queries to extract metadata fields (`source`, `clickType`, `platform`).

**DateRangeHelper**

Provides `applyToQuery()` for applying `dateRange` filters (`today`, `last7days`, `last30days`, etc.) to Yii query objects.

**ExportHelper**

Handles CSV, JSON, and Excel export file generation with consistent date formatting. Used by `AnalyticsController` for analytics exports.

**CpNavHelper**

Provides `buildSubnav()` for building the plugin CP subnav with permission-based visibility, and `firstAccessibleRoute()` for redirecting users with partial permissions.

---

## `lindemannrock/logging-library`

| Feature | Description |
|---------|-------------|
| `LoggingTrait` | Convenient logging methods (logInfo, logWarning, logError, logDebug) |
| `LoggingLibrary::addLogsNav()` | Adds "Logs" subnav to plugin CP navigation |

### Details

**LoggingTrait**

Provides standardized logging to dedicated plugin log files

**LoggingLibrary::addLogsNav()**

View plugin logs directly in the Control Panel
