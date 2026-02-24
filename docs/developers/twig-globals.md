# Twig Globals

SmartLink Manager provides the following global variables in your Twig templates.

## `smartlinkHelper`

*Provided by `lindemannrock/base`*

| Property | Description |
|----------|-------------|
| `smartlinkHelper.displayName` | Display name (singular, without "Manager") |
| `smartlinkHelper.pluralDisplayName` | Plural display name (without "Manager") |
| `smartlinkHelper.fullName` | Full plugin name (as configured) |
| `smartlinkHelper.lowerDisplayName` | Lowercase display name (singular) |
| `smartlinkHelper.pluralLowerDisplayName` | Lowercase plural display name |

### Examples

```twig
{{ smartlinkHelper.displayName }}
{{ smartlinkHelper.pluralDisplayName }}
{{ smartlinkHelper.fullName }}
{{ smartlinkHelper.lowerDisplayName }}
{{ smartlinkHelper.pluralLowerDisplayName }}
```
