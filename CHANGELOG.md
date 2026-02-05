# Changelog

## [5.20.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.19.0...v5.20.0) (2026-02-05)


### Features

* **analytics:** enhance analytics functionality and UI ([571b50e](https://github.com/LindemannRock/craft-smartlink-manager/commit/571b50e7fbe1e0b4d0a83fe91f26fa85f4b96b40))


### Bug Fixes

* **SmartLinkManager:** update [@since](https://github.com/since) version in getCpSections method to 5.20.0 ([9914e5f](https://github.com/LindemannRock/craft-smartlink-manager/commit/9914e5f0008cbaaaa6910e1bc874921fa74fe889))


### Miscellaneous Chores

* **dependencies:** Remove matomo/device-detector from composer.json ([6c1d244](https://github.com/LindemannRock/craft-smartlink-manager/commit/6c1d24465999c5c2c7b48203d3ea7f9680fa26fb))
* update package-lock.json and package.json for dependency management ([af7f66f](https://github.com/LindemannRock/craft-smartlink-manager/commit/af7f66f870ca8dcc0af628083cc36d78248121bf))

## [5.19.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.18.0...v5.19.0) (2026-01-26)


### Features

* replace direct Craft plugin calls with PluginHelper methods ([c9a6610](https://github.com/LindemannRock/craft-smartlink-manager/commit/c9a6610bdab6065ba8c1233767ef7035fcec116d))


### Bug Fixes

* **jobs:** prevent duplicate scheduling of CleanupAnalyticsJob ([afa64e1](https://github.com/LindemannRock/craft-smartlink-manager/commit/afa64e1973e045f04598b6826942c70c1a2afbfd))

## [5.18.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.17.0...v5.18.0) (2026-01-21)


### Features

* Add configurable geo IP provider settings with HTTPS support ([2b8718c](https://github.com/LindemannRock/craft-smartlink-manager/commit/2b8718cc2ed4b86d803f222ef772e9d68d79676c))


### Bug Fixes

* correct header title from "Plugin Settings" to "General Settings" ([450acff](https://github.com/LindemannRock/craft-smartlink-manager/commit/450acff76e4764eb1a090576e8016babd0078baa))

## [5.17.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.16.1...v5.17.0) (2026-01-17)


### Features

* enhance searchable attributes with image title and filename ([7a8e9fe](https://github.com/LindemannRock/craft-smartlink-manager/commit/7a8e9fea1b0c3f48b3ef5550486b2793b64bc369))

## [5.16.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.16.0...v5.16.1) (2026-01-16)


### Bug Fixes

* reorganize and standardize analytics templates ([82de60e](https://github.com/LindemannRock/craft-smartlink-manager/commit/82de60ee81e467f13687fb7533fc9ab6a42f84e7))
* update cache location message to use smartlinkHelper for dynamic path ([3be1ba9](https://github.com/LindemannRock/craft-smartlink-manager/commit/3be1ba9f1daddcef99800a2097d35991d57ba9c7))
* update cache location path to use plugin handle for dynamic storage ([58b9b1b](https://github.com/LindemannRock/craft-smartlink-manager/commit/58b9b1bb8fd7fe0e17ff7d84511cc6141e3bc4df))
* Update filename generation for analytics export to use lower display name ([8a30175](https://github.com/LindemannRock/craft-smartlink-manager/commit/8a30175e46ef15ee3a204138be6096ef703f77d7))
* update hardcoded cache paths with PluginHelper for consistency ([73352ab](https://github.com/LindemannRock/craft-smartlink-manager/commit/73352ab023f5cdaabc53350b2f2e5ed1dfc3359a))
* update PluginHelper bootstrap to include download permissions for logging ([a858d0a](https://github.com/LindemannRock/craft-smartlink-manager/commit/a858d0a382a2c49619be74fb9208c94171e6ee53))

## [5.16.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.15.1...v5.16.0) (2026-01-12)


### Features

* Auto-format slug on blur for improved user experience ([265d390](https://github.com/LindemannRock/craft-smartlink-manager/commit/265d3905f100c9a8d971501a73ad95705080555f))
* Update link display and interaction metrics in top links widget ([8f5615b](https://github.com/LindemannRock/craft-smartlink-manager/commit/8f5615bfbeb0e928cf88284ffa6d5320a1e31ac0))


### Bug Fixes

* Format cache file counts and total clicks in cache management buttons ([cc6e401](https://github.com/LindemannRock/craft-smartlink-manager/commit/cc6e4017e7f83888312c7cd30fa02dfac9ddebf1))

## [5.15.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.15.0...v5.15.1) (2026-01-11)


### Bug Fixes

* Update label retrieval to use getFullName() for consistency ([a86187c](https://github.com/LindemannRock/craft-smartlink-manager/commit/a86187c1a824df2b751037a106c2d57a866a5881))


### Miscellaneous Chores

* Update README with enhanced multi-site support and clarify smart link fields ([9d5ace9](https://github.com/LindemannRock/craft-smartlink-manager/commit/9d5ace9d778cf8e253e7e9950c21e4c010a4de79))

## [5.15.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.14.0...v5.15.0) (2026-01-10)


### Features

* Replace custom country name retrieval with GeoHelper utility ([31ea2f3](https://github.com/LindemannRock/craft-smartlink-manager/commit/31ea2f3e1fb4490ee08a95f4071376fb359e9a7b))

## [5.14.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.13.0...v5.14.0) (2026-01-09)


### Features

* update filename format for exports and add JSON export option in analytics ([b30637e](https://github.com/LindemannRock/craft-smartlink-manager/commit/b30637e953490a47e807754992f172869fcefdf1))

## [5.13.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.12.2...v5.13.0) (2026-01-08)


### Features

* refactor permissions to use grouped nested structure with granular access control ([f506873](https://github.com/LindemannRock/craft-smartlink-manager/commit/f5068737def897c8c5c08ff5ad2c9cc639998ef5))


### Bug Fixes

* CP nav visibility and index routing for non-primary enabled sites ([f8803a6](https://github.com/LindemannRock/craft-smartlink-manager/commit/f8803a64c9d77b08c5bcc188c908bd77d1ef834c))

## [5.12.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.12.1...v5.12.2) (2026-01-07)


### Bug Fixes

* rename SmartLinksController to SmartlinksController for Linux compatibility ([d3d03bd](https://github.com/LindemannRock/craft-smartlink-manager/commit/d3d03bd087132ed31d15bfe258cd2bc2f4f092d2))

## [5.12.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.12.0...v5.12.1) (2026-01-07)


### Bug Fixes

* remove unnecessary blank lines in SmartLinksController ([d6da71c](https://github.com/LindemannRock/craft-smartlink-manager/commit/d6da71c3ad729b09e2d5e269ad00cb31af04dfd3))

## [5.12.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.11.1...v5.12.0) (2026-01-06)


### Features

* rename plugin from Smart Links to SmartLink Manager ([e87e641](https://github.com/LindemannRock/craft-smartlink-manager/commit/e87e64115d6f485f4a986485ec772bd525224cc8))
* rename plugin from Smart Links to SmartLink Manager ([0b0633a](https://github.com/LindemannRock/craft-smartlink-manager/commit/0b0633aeda093828b6db4c5c28c3e203cfe32a2b))

## [5.11.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.11.0...v5.11.1) (2026-01-06)


### Bug Fixes

* add craftcms/ecs to require-dev for enhanced development tools ([24402e3](https://github.com/LindemannRock/craft-smartlink-manager/commit/24402e3d412dcf6e818b07a4210274af5dadf2e7))

## [5.11.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.10.2...v5.11.0) (2026-01-06)


### Features

* migrate to shared base plugin ([06fe2f9](https://github.com/LindemannRock/craft-smartlink-manager/commit/06fe2f9d7fc03765c64358a09c801d48970dbf88))


### Bug Fixes

* change URL fields to text type for better data handling ([96c8087](https://github.com/LindemannRock/craft-smartlink-manager/commit/96c80871af95f9a0d494d6f9975c0841857a20fa))

## [5.10.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.10.1...v5.10.2) (2026-01-05)


### Bug Fixes

* add tab-content class to analytics sections for improved styling ([76de310](https://github.com/LindemannRock/craft-smartlink-manager/commit/76de3103e3e2c0ca8b1f796cd010fcadd23f0fef))

## [5.10.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.10.0...v5.10.1) (2025-12-19)


### Bug Fixes

* filter sites for selector to respect enabledSites setting ([0407773](https://github.com/LindemannRock/craft-smartlink-manager/commit/0407773ea8b953f712ec65ea14f5339236abd7c7))

## [5.10.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.9.5...v5.10.0) (2025-12-19)


### Features

* Add geographic, overview, and traffic devices analytics tabs ([a7e4ed7](https://github.com/LindemannRock/craft-smartlink-manager/commit/a7e4ed78ad2019924bcd2c06d77012f6e52454ba))


### Bug Fixes

* enhance cache duration settings with human-readable format and validation ([0337cd2](https://github.com/LindemannRock/craft-smartlink-manager/commit/0337cd28fd5fdd917940701a39ed156ad9497db4))
* improve country name retrieval by adding missing ISO codes and handling empty input ([dcd9388](https://github.com/LindemannRock/craft-smartlink-manager/commit/dcd938803afe79de0faf19b0a8a2ed6f08a2cc50))
* reorder default table attributes to include 'status' for better clarity ([b472235](https://github.com/LindemannRock/craft-smartlink-manager/commit/b472235bb5236c878fe8a5ae93fd3263c5b89a9e))
* update display name handling to trim whitespace and improve clarity ([cb35971](https://github.com/LindemannRock/craft-smartlink-manager/commit/cb35971e3eba8162289662755980cb02c8cd6119))

## [5.9.5](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.9.4...v5.9.5) (2025-12-16)


### Bug Fixes

* update time formatting in analytics dashboard to use locale settings ([b2ce9ee](https://github.com/LindemannRock/craft-smartlink-manager/commit/b2ce9ee520e32176d14fa861ee1873fcbdacdc66))

## [5.9.4](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.9.3...v5.9.4) (2025-12-16)


### Bug Fixes

* update redirect manager events to only include slug-change ([2e7a31f](https://github.com/LindemannRock/craft-smartlink-manager/commit/2e7a31f26562e15c6c3f8209b4dfbec957bfd4f1))

## [5.9.3](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.9.2...v5.9.3) (2025-12-16)


### Bug Fixes

* update QR code handling to redirect when disabled and conditionally display options ([13b8cb8](https://github.com/LindemannRock/craft-smartlink-manager/commit/13b8cb892cbafc5f84ddc5573172f62ef5a28758))

## [5.9.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.9.1...v5.9.2) (2025-12-16)


### Bug Fixes

* correct anchor tag formatting in settings layout sidebar ([6e8a8c4](https://github.com/LindemannRock/craft-smartlink-manager/commit/6e8a8c4227e63a1eea86869eba87d85d00da884e))

## [5.9.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.9.0...v5.9.1) (2025-12-16)


### Bug Fixes

* correct variable name and improve sidebar markup in settings layout ([80bf6f0](https://github.com/LindemannRock/craft-smartlink-manager/commit/80bf6f02c500d2b5a748c02e5ad2bca9793a57cf))

## [5.9.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.8.0...v5.9.0) (2025-12-16)


### Features

* add cache storage method configuration for different environments ([e23f34b](https://github.com/LindemannRock/craft-smartlink-manager/commit/e23f34b05572d80dc0036ce82571187afac239af))
* add cache storage method configuration to settings ([5f517c1](https://github.com/LindemannRock/craft-smartlink-manager/commit/5f517c12a78e9b4d76cb6b3022bfa97e0e9391f6))
* enhance analytics by including detailed link status counts and conditional cache file counting ([0f4251e](https://github.com/LindemannRock/craft-smartlink-manager/commit/0f4251e0a6cbf6beab440bb6ca2de51ba75252cf))
* enhance analytics by preserving date range on redirect and converting date/time to user's timezone ([c7fd6f8](https://github.com/LindemannRock/craft-smartlink-manager/commit/c7fd6f8bf10afdbc9ef7cb065813bbfa7bf29d85))
* enhance analytics display with number formatting and improved cache status overview ([36b5598](https://github.com/LindemannRock/craft-smartlink-manager/commit/36b5598a49e8dc87366100433e273cb1c32a6d1a))
* implement cache storage method selection and handling for Redis and file systems ([1fc4b36](https://github.com/LindemannRock/craft-smartlink-manager/commit/1fc4b3696872927aa5ace3c25e5e238ab36cb16a))


### Bug Fixes

* update Redis cache display by removing redundant text and adjusting styles ([b9fbb45](https://github.com/LindemannRock/craft-smartlink-manager/commit/b9fbb459d34c41da3f02dd49cfdd132fc1813f67))

## [5.8.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.7.2...v5.8.0) (2025-12-03)


### Features

* add docTitle variable to enhance page titles in CP layouts ([6e5434b](https://github.com/LindemannRock/craft-smartlink-manager/commit/6e5434b57a7942c5c51186b345d79fd73310e59c))
* add Info Box component for displaying informational notices ([ea8f1be](https://github.com/LindemannRock/craft-smartlink-manager/commit/ea8f1be456a887992e78a98ce5c6478cf840f041))
* update analytics display to show top 20 links and recent interactions ([a5a9089](https://github.com/LindemannRock/craft-smartlink-manager/commit/a5a9089bdfc97be865bcc433151f9da84f4799d4))


### Bug Fixes

* improve site name retrieval in AnalyticsService for better accuracy ([b5640dc](https://github.com/LindemannRock/craft-smartlink-manager/commit/b5640dc398bcd2c57ad1b831f3c0b8fd2095606a))


### Miscellaneous Chores

* add [@since](https://github.com/since) 1.0.0 annotations to various files for version tracking ([4f0373c](https://github.com/LindemannRock/craft-smartlink-manager/commit/4f0373cc702ff2c5c7ef54d3776a9a5d6a98f8c0))

## [5.7.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.7.1...v5.7.2) (2025-11-23)


### Bug Fixes

* date range parameter for getRecentClicks method and update template usage ([aae76ad](https://github.com/LindemannRock/craft-smartlink-manager/commit/aae76ad478c187342b9371ccbb05b6cc87b5784a))

## [5.7.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.7.0...v5.7.1) (2025-11-23)


### Bug Fixes

* 404 handling through Redirect Manager integration ([9d9c233](https://github.com/LindemannRock/craft-smartlink-manager/commit/9d9c233748d436a210a719dfad4504870f632b56))

## [5.7.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.6.2...v5.7.0) (2025-11-23)


### Features

* **analytics:** add default location settings for local development ([f719cd1](https://github.com/LindemannRock/craft-smartlink-manager/commit/f719cd1984d98a25f649d55782d6e3016609134a))


### Bug Fixes

* **docs:** clarify cache duration and detection method descriptions ([e66285b](https://github.com/LindemannRock/craft-smartlink-manager/commit/e66285b0200774208042dd1bd7d967f7b8490625))

## [5.6.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.6.1...v5.6.2) (2025-11-15)


### Miscellaneous Chores

* **license:** add MIT License file ([e4962e1](https://github.com/LindemannRock/craft-smartlink-manager/commit/e4962e1c4deb48d9b0085d7e6699f4c00b8e0515))

## [5.6.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.6.0...v5.6.1) (2025-11-14)


### Bug Fixes

* **analytics:** improve site name display for clicks by checking site ID ([d27ce51](https://github.com/LindemannRock/craft-smartlink-manager/commit/d27ce511301939b2981f1511bcfa8d34428ce372))

## [5.6.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.5.3...v5.6.0) (2025-11-14)


### Features

* **integrations:** add option to handle expired smart links in redirect manager settings ([cf7aaf3](https://github.com/LindemannRock/craft-smartlink-manager/commit/cf7aaf388eada6ee86903af540cdec3291ed7c35))
* **smartlinks:** add copy URL functionality for smart link redirect ([f43f76e](https://github.com/LindemannRock/craft-smartlink-manager/commit/f43f76e437b75df0301a2faa9cb010149a3eae24))
* **smartlinks:** enhance site context handling for smart links and add validation for enabled sites ([30dd959](https://github.com/LindemannRock/craft-smartlink-manager/commit/30dd9590a5c9e33e96ede05ee0c18501883874b3))


### Bug Fixes

* **migrations:** add redirect manager events field to smartlinks settings ([86779fb](https://github.com/LindemannRock/craft-smartlink-manager/commit/86779fbb4b9063189b778e18ddef009349c82b16))

## [5.5.3](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.5.2...v5.5.3) (2025-11-11)


### Bug Fixes

* **ip-salt-error:** enhance error message with copyable commands for generating IP hash salt ([b19129c](https://github.com/LindemannRock/craft-smartlink-manager/commit/b19129c5b18b6110d68b7664caeba71e5ce9f243))

## [5.5.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.5.1...v5.5.2) (2025-11-11)


### Bug Fixes

* **qrPrefix:** Update QR code URL prefix to support nested patterns ([401a642](https://github.com/LindemannRock/craft-smartlink-manager/commit/401a642ed15a60f225221d9626b1d6fb45a0f2f3))

## [5.5.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.5.0...v5.5.1) (2025-11-10)


### Bug Fixes

* **analytics:** Update export link handling and streamline export functionality ([bcf960b](https://github.com/LindemannRock/craft-smartlink-manager/commit/bcf960bb209c49f95297ce9b2410844d8c423f8e))

## [5.5.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.4.1...v5.5.0) (2025-11-10)


### Features

* **SEOMaticIntegration:** Enhance QR Code and Redirect Templates with SEOmatic Integration ([2fb9fdf](https://github.com/LindemannRock/craft-smartlink-manager/commit/2fb9fdf9e4f832bac3ae4e131e06dcf2230750f4))

## [5.4.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.4.0...v5.4.1) (2025-11-07)


### Bug Fixes

* CleanupAnalyticsJob with next run time calculation and display ([ff1beb4](https://github.com/LindemannRock/craft-smartlink-manager/commit/ff1beb4d264d5ccc5283feee2af7e9eb9262ecf4))

## [5.4.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.3.3...v5.4.0) (2025-11-06)


### Features

* Add Redirect Manager integration with centralized redirect handling ([d345dcc](https://github.com/LindemannRock/craft-smartlink-manager/commit/d345dcc1a10cec478f8a72e0588798d2524afc60))
* enhance settings management with new integration handling and improved save logic ([98b9c3e](https://github.com/LindemannRock/craft-smartlink-manager/commit/98b9c3ec417721a16fe484c5580246481ce9bdc5))


### Bug Fixes

* add comprehensive documentation and configuration options for Smart Links plugin ([fc7b0ca](https://github.com/LindemannRock/craft-smartlink-manager/commit/fc7b0cad635fc05858e5873203ce70173ec7237c))
* integration status display and rename Redirect settings to Behavior ([3566537](https://github.com/LindemannRock/craft-smartlink-manager/commit/356653756cfc2aec0b916555f12bbccc4db7a403))
* SEOmatic integration display with dynamic plugin name and updated description ([c73c8b5](https://github.com/LindemannRock/craft-smartlink-manager/commit/c73c8b58a2e1907af0fb37f00c1d90d4c7714329))

## [5.3.3](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.3.2...v5.3.3) (2025-10-26)


### Bug Fixes

* reset session warning when devMode is enabled to allow re-display of warnings ([3c11269](https://github.com/LindemannRock/craft-smartlink-manager/commit/3c11269e321e298296b81b88c39287351b67e46c))

## [5.3.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.3.1...v5.3.2) (2025-10-26)


### Bug Fixes

* improve configuration file structure for better readability and organization ([d1deaa2](https://github.com/LindemannRock/craft-smartlink-manager/commit/d1deaa2c1555c8b556ea7a70f0ea23795661737c))
* reorganize configuration settings for clarity and maintainability ([f98f534](https://github.com/LindemannRock/craft-smartlink-manager/commit/f98f534a64f212630f0306fa0f3d8aa055382751))

## [5.3.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.3.0...v5.3.1) (2025-10-26)


### Bug Fixes

* update QR code preview width to max-width for better responsiveness ([56140ac](https://github.com/LindemannRock/craft-smartlink-manager/commit/56140ac846407e3638b36f9ea4c2aff6889fda4e))

## [5.3.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.2.0...v5.3.0) (2025-10-26)


### Features

* add dashboard widgets, per-link analytics toggle, and worldwide location support ([9447bb5](https://github.com/LindemannRock/craft-smartlink-manager/commit/9447bb5ad04e68aa764c5f97887835819d65cbc7))
* enhance analytics cleanup scheduling and UI integration ([08ac107](https://github.com/LindemannRock/craft-smartlink-manager/commit/08ac107a79b04e578108b350c59c9d4cd08526d7))
* enhance analytics templates and services with geo detection settings and improved data handling ([7d2485d](https://github.com/LindemannRock/craft-smartlink-manager/commit/7d2485d9723f41ddd418613e3f6c5d77e2025597))
* enhance QR code functionality with new methods and improved color handling ([51e25b4](https://github.com/LindemannRock/craft-smartlink-manager/commit/51e25b4a0a4ade16d26205bf89ab5bbd307893b9))
* enhance templates with dynamic plugin name usage for better localization ([e347d59](https://github.com/LindemannRock/craft-smartlink-manager/commit/e347d59e7ac4a19d11202e7f07b02ee44ca3bc4a))
* implement logging improvements across various components using LoggingTrait ([bde1785](https://github.com/LindemannRock/craft-smartlink-manager/commit/bde17852dab9bdb76262761d270b7cb980f3eaf9))
* update analytics templates to display site names and improve data presentation ([25e631d](https://github.com/LindemannRock/craft-smartlink-manager/commit/25e631d4117cf440727c3f6aec275bca5a51639e))
* update QR code settings to support inheritance and null values for colors ([137b5c1](https://github.com/LindemannRock/craft-smartlink-manager/commit/137b5c12c438fcedd1272e156573c699accb38fb))


### Bug Fixes

* Handle missing IP hash salt gracefully in analytics tracking ([7170942](https://github.com/LindemannRock/craft-smartlink-manager/commit/71709424411a3c5ca8d3c1aae91e62ba6b2f2969))
* simplify utility page and improve settings ([262d595](https://github.com/LindemannRock/craft-smartlink-manager/commit/262d5959de4d339d87365b1dbd05083fcb6dc757))
* update QR code preview dimensions and adjust padding for improved layout ([1e41165](https://github.com/LindemannRock/craft-smartlink-manager/commit/1e411652bca56c5fa5bf1ad60cde1db297aebf70))
* update subnav label to a static value for clarity ([7ee4682](https://github.com/LindemannRock/craft-smartlink-manager/commit/7ee468264c4f986e3427ca6222cb6efba81a4913))

## [5.2.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.1.0...v5.2.0) (2025-10-22)


### Features

* Add itemsPerPage setting to plugin configuration ([94f0549](https://github.com/LindemannRock/craft-smartlink-manager/commit/94f0549190da9f5664fb1196504fedb861c7944b))


### Miscellaneous Chores

* Remove backup template for smart links index ([68a5f57](https://github.com/LindemannRock/craft-smartlink-manager/commit/68a5f5757796c5d6e9a2c70f45971adb61e02f46))

## [5.1.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.0.1...v5.1.0) (2025-10-22)


### Features

* Add IP privacy protection with salted hashing and optional anonymization ([e81774c](https://github.com/LindemannRock/craft-smartlink-manager/commit/e81774cd42d27c642fee37866dd798dfe9ec8092))


### Bug Fixes

* Improve IP salt validation and update to App::env() pattern ([70d3858](https://github.com/LindemannRock/craft-smartlink-manager/commit/70d3858ef318dfc0edadf0cf57eea407bb55cbf9))

## [5.0.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v5.0.0...v5.0.1) (2025-10-20)


### Miscellaneous Chores

* update logging library dependency to version 5.0 and enhance README with additional badges ([ca3e21d](https://github.com/LindemannRock/craft-smartlink-manager/commit/ca3e21db208577b6789164b56eeb2c2b72b53368))

## [5.0.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.24.0...v5.0.0) (2025-10-20)


### Miscellaneous Chores

* bump version scheme to match Craft 5 ([447ef00](https://github.com/LindemannRock/craft-smartlink-manager/commit/447ef00dc7bdbeb1fdb757e7cdba1d4434600307))

## [1.24.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.23.0...v1.24.0) (2025-10-17)


### Features

* Use dynamic plugin name from settings for logging configuration ([081a9a0](https://github.com/LindemannRock/craft-smartlink-manager/commit/081a9a0185b90ffe967028a4e55499b461ea3bf2))

## [1.23.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.22.1...v1.23.0) (2025-10-17)


### Features

* Add dynamic plugin name support for complete rebranding ([fbbadd1](https://github.com/LindemannRock/craft-smartlink-manager/commit/fbbadd111d102613d72a37ac5e6063e93785bcd3))
* add SEOmatic integration for client-side analytics tracking ([56d9b17](https://github.com/LindemannRock/craft-smartlink-manager/commit/56d9b1709eb8e443bab1cefa2f69fdd73bc99922))

## [Unreleased]

### Features

* **integrations:** add SEOmatic integration for pushing events to Google Tag Manager data layer
  - New modular integration architecture at `/src/integrations/`
  - SEOmatic plugin detection and status checking
  - Push Smart Links click events to GTM/Google Analytics via SEOmatic
  - Configurable event types (redirect, button_click, qr_scan)
  - Customizable event prefix for GTM event names
  - Comprehensive event data including device, platform, geographic, and source tracking
  - Settings UI in Analytics page for easy configuration
  - Zero performance impact when disabled or SEOmatic not installed
  - Fully documented with README section and GTM trigger examples

## [1.22.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.22.0...v1.22.1) (2025-10-16)


### Bug Fixes

* update installation instructions for Composer and DDEV ([e544109](https://github.com/LindemannRock/craft-smartlink-manager/commit/e5441096ee51895df0d91dc19e2b194e73cac03f))

## [1.22.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.21.0...v1.22.0) (2025-10-16)


### Features

* **dependencies:** add lindemannrock/craft-logging-library as a requirement ([93338df](https://github.com/LindemannRock/craft-smartlink-manager/commit/93338df51294a2ddd23a11b81ff01728e49a0183))

## [1.21.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.20.0...v1.21.0) (2025-10-16)


### Features

* **logging:** add detailed logging configuration and documentation ([be6f11a](https://github.com/LindemannRock/craft-smartlink-manager/commit/be6f11a18ab8705578119abebef941158711a327))

## [1.20.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.19.4...v1.20.0) (2025-10-16)


### Features

* integrate LindemannRock Logging Library with structured PSR-3 logging across all controllers and services ([3cd09c5](https://github.com/LindemannRock/craft-smartlink-manager/commit/3cd09c59e752ddd052740c968c14a008d90117f5))

## [1.19.4](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.19.3...v1.19.4) (2025-10-02)


### Bug Fixes

* remove random salt from IP hashing to accurately count unique visitors ([02f1c8b](https://github.com/LindemannRock/craft-smartlink-manager/commit/02f1c8b80f8cf23eec3fe25200ee50b0d8a341ec))

## [1.19.3](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.19.2...v1.19.3) (2025-10-02)


### Bug Fixes

* remove clicks column references and resolve duplicate analytics entries ([78b933a](https://github.com/LindemannRock/craft-smartlink-manager/commit/78b933a2cae7c78bb30e1697e4e61d6823c09600))

## [1.19.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.19.1...v1.19.2) (2025-10-02)


### Bug Fixes

* handle NULL and incorrect platform values in analytics chart and cleanup ([4cf21be](https://github.com/LindemannRock/craft-smartlink-manager/commit/4cf21be8971d5ba7f010fc96cd708cbb97729ad3))

## [1.19.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.19.0...v1.19.1) (2025-10-02)


### Features

* add checkbox group for enabling Smart Links on specific sites ([a0d6f85](https://github.com/LindemannRock/craft-smartlink-manager/commit/a0d6f8586d7135625128b61857bc50d52abcd46d))
* add configurable URL prefixes for smart links and QR codes ([f7239b2](https://github.com/LindemannRock/craft-smartlink-manager/commit/f7239b2f47d3e3329c1d0bc4dc181e69eb033b4d))
* add CSRF token refresh for cached pages and fix metadata serialization ([c22c2b1](https://github.com/LindemannRock/craft-smartlink-manager/commit/c22c2b138e3c93382621cb7f1fcaaf9999a4c898))
* add custom QR code template settings and update related translations ([c362642](https://github.com/LindemannRock/craft-smartlink-manager/commit/c362642eb71a064e27da7cbc360225efe100ae3e))
* add customizable URL prefixes and templates for smart links and QR codes ([eff264d](https://github.com/LindemannRock/craft-smartlink-manager/commit/eff264d7cc39d6d81d622f1628978a6d261ef28f))
* add enabledSites property to Settings model for site-specific Smart Links configuration ([828b105](https://github.com/LindemannRock/craft-smartlink-manager/commit/828b105f4fc2335edec9227be4b0a81198233e31))
* Add Field Layout support to Smart Links element type ([7b77015](https://github.com/LindemannRock/craft-smartlink-manager/commit/7b77015311250dd08af76b7069c8bb3e0d8377eb))
* Add field layout support with project config sync ([21e0ba8](https://github.com/LindemannRock/craft-smartlink-manager/commit/21e0ba8551bd5a7c58e603e93db3651cadcac3cc))
* Add interaction type breakdown to Performance card ([9c47423](https://github.com/LindemannRock/craft-smartlink-manager/commit/9c47423dc73360e24710ff79fcf001badf0d5de9))
* add multi-site management and site selection configuration for Smart Links ([304ebc1](https://github.com/LindemannRock/craft-smartlink-manager/commit/304ebc1470760ad2e8e7f66d11996358bc81f279))
* add plugin credit component to settings and analytics templates ([c22cf96](https://github.com/LindemannRock/craft-smartlink-manager/commit/c22cf96fde791c79b1e650964985cf44f8beeba6))
* add QR code cache busting setting to fix tracking with CDN caching ([72eac94](https://github.com/LindemannRock/craft-smartlink-manager/commit/72eac947123e427262617346103543810347fb4d))
* Add read-only mode for Smart Links settings when allowAdminChanges is disabled ([a9ad703](https://github.com/LindemannRock/craft-smartlink-manager/commit/a9ad70344ceaf8b304b848de739600a2d0d00e90))
* add site settings and default settings row to smartlinks_settings table ([c143d41](https://github.com/LindemannRock/craft-smartlink-manager/commit/c143d41a1fb7d5b2cd0c2d8deb254284a5bff4e2))
* add Smart Links utility template with link statistics and recent analytics ([acf62c7](https://github.com/LindemannRock/craft-smartlink-manager/commit/acf62c7ad344275381fdce7cfbefa74b8f674591))
* enhance CSRF token response with device detection information ([5af440b](https://github.com/LindemannRock/craft-smartlink-manager/commit/5af440ba912c25b3e97df877cf8f60de1747af26))
* enhance README with additional features for image management and landing page customization ([8162b36](https://github.com/LindemannRock/craft-smartlink-manager/commit/8162b36ffec42db3b9701d2cf6dd96cf92f9617f))
* enhance settings handling with additional debug logging and auto-setting for qrLogoVolumeUid ([a3b7d71](https://github.com/LindemannRock/craft-smartlink-manager/commit/a3b7d7112493de5c0c56a27c29914bf02c87768a))
* enhance settings UI with URL and template configuration options for smart links and QR codes ([239219d](https://github.com/LindemannRock/craft-smartlink-manager/commit/239219d1c4449067f558148b5bab2d1ca0ae7d88))
* implement site-specific Smart Links functionality and enable site selection in templates ([6c87105](https://github.com/LindemannRock/craft-smartlink-manager/commit/6c871052fcfa89f39611b97ed62c4bd2d1a04d60))
* Improve analytics data management and platform display ([d60def7](https://github.com/LindemannRock/craft-smartlink-manager/commit/d60def7a5fa4014e6ed9a201e251bedc28879c4f))
* initial Smart Links plugin implementation ([6b5c0ed](https://github.com/LindemannRock/craft-smartlink-manager/commit/6b5c0ed5911f8ecdb803cb0c76395fdce7bb03ef))
* refactor analytics tracking to client-side JavaScript for CDN compatibility ([edfd7a9](https://github.com/LindemannRock/craft-smartlink-manager/commit/edfd7a91bccb7bacc0caeba9ea805e59c2b3cf42))
* Register project config event handlers and save field layout UID ([3490026](https://github.com/LindemannRock/craft-smartlink-manager/commit/34900265fa6609fd8fbc092d67fa53100dab01dc))
* remove redundant enabled and clicks columns from smartlinks table ([ec79d43](https://github.com/LindemannRock/craft-smartlink-manager/commit/ec79d43e4a0b28e4415150a3d6297cdbbe4c069e))
* update caching strategy in RedirectController to vary by device type ([9bb8e4b](https://github.com/LindemannRock/craft-smartlink-manager/commit/9bb8e4bd881509e72fb5f8f60f2c8d9726ddfbc1))
* update README and migration for site settings in Smart Links ([c309b1b](https://github.com/LindemannRock/craft-smartlink-manager/commit/c309b1b98e1a00b75f09c039b6054c736e0ed1b5))


### Bug Fixes

* enabled status requiring two saves to work ([19a7723](https://github.com/LindemannRock/craft-smartlink-manager/commit/19a77233e4ce6add702e903c453217b4d0392fd5))
* enabled status requiring two saves to work ([1106a02](https://github.com/LindemannRock/craft-smartlink-manager/commit/1106a028603c6c0886800ad78cc0822ba66f3b2f))
* force new release for enabled status fix ([202e1fd](https://github.com/LindemannRock/craft-smartlink-manager/commit/202e1fdb4e15ad96dd56ad261ec57c0b13ff9c17))
* handle empty QR logo and image IDs in SmartLinksController ([d9a7e65](https://github.com/LindemannRock/craft-smartlink-manager/commit/d9a7e65055ca27534f382ad29aec7a95eeaa10e7))
* improve description in CleanupAnalyticsJob and format .gitignore entries ([3a58cbc](https://github.com/LindemannRock/craft-smartlink-manager/commit/3a58cbc9cd5403b2413e9a644ec7b7026baab72f))
* improve tracking and analytics display ([d94701c](https://github.com/LindemannRock/craft-smartlink-manager/commit/d94701c5290c2323bc811a7b1acdf0fd5a8a6f48))
* make redirects truly cache-safe by moving URL selection to client-side ([bdbfa15](https://github.com/LindemannRock/craft-smartlink-manager/commit/bdbfa15bacdaf5484602b10e623f935420c509d9))
* multi-site analytics tracking ([493bbc4](https://github.com/LindemannRock/craft-smartlink-manager/commit/493bbc427bca5b0ba4e4575f88c4d98ef1405ac9))
* Preserve QR source parameter and display destination URLs in analytics ([a579481](https://github.com/LindemannRock/craft-smartlink-manager/commit/a579481cb2fc65772a47fc380405b8c106527f78))
* remove development backups and IDE files ([f078fdb](https://github.com/LindemannRock/craft-smartlink-manager/commit/f078fdb024b40398b2ad93c9d9499ffc9172a021))
* replace sendBeacon with fetch POST for CDN compatibility ([71a62dd](https://github.com/LindemannRock/craft-smartlink-manager/commit/71a62dd917f1a1d8ec8d4b9bf97b8ac11708af59))
* Show read-only notice only on Field Layout settings page ([049d7ca](https://github.com/LindemannRock/craft-smartlink-manager/commit/049d7ca021d451ccf7756a1e06af4c4b73949924))
* smart link tracking to work with static page caching ([1fb2774](https://github.com/LindemannRock/craft-smartlink-manager/commit/1fb2774df9761bd8b8c5c7aaad9cf925ed969add))
* Smart Links database schema to match working installation ([03fe1dd](https://github.com/LindemannRock/craft-smartlink-manager/commit/03fe1dd45e8985bafe8996f3b38dde2d01740057))
* trigger release for enabled status fix ([3daded7](https://github.com/LindemannRock/craft-smartlink-manager/commit/3daded757027584bfc2a855fddd6e88f239a650a))
* update copyright notice in LICENSE file ([3a2531c](https://github.com/LindemannRock/craft-smartlink-manager/commit/3a2531cd2086d5dddc2e7a16905ed3ae6fa35f05))
* update device detection method in RedirectController ([198fc1a](https://github.com/LindemannRock/craft-smartlink-manager/commit/198fc1acadd5a050052b2c1ca8db9343bfea914e))
* update device detection method in RedirectController ([3e7fb1a](https://github.com/LindemannRock/craft-smartlink-manager/commit/3e7fb1abcfd76bbbbecd9fb4bfca2706edbf47c9))
* update displayName method to return plugin name and rename iconPath to icon ([aca60a0](https://github.com/LindemannRock/craft-smartlink-manager/commit/aca60a06bc689820a2d407270541e1c4222d5853))
* update instruction for custom redirect template field ([de0a299](https://github.com/LindemannRock/craft-smartlink-manager/commit/de0a299fd959ff56f2f8a48357e0e3424455548f))
* update PHP requirement from ^8.0.2 to ^8.2 in composer.json ([29d375d](https://github.com/LindemannRock/craft-smartlink-manager/commit/29d375d857f2f3eb9277318c24150ac3034e1120))
* update repository links in README and composer.json to reflect new naming ([a239296](https://github.com/LindemannRock/craft-smartlink-manager/commit/a239296fbe4e9cc70bd86863bd89fbcec3031043))
* update requirements in README for clarity and consistency ([a17ca25](https://github.com/LindemannRock/craft-smartlink-manager/commit/a17ca2501f162c2c60df0f82449f142f5337d7e3))
* update site selection logic in multi-site configuration ([d2bd97b](https://github.com/LindemannRock/craft-smartlink-manager/commit/d2bd97baae4bd6f865f9da53621e581f12e36cca))
* Update URL assignment to check both redirectUrl and buttonUrl formats ([832f196](https://github.com/LindemannRock/craft-smartlink-manager/commit/832f1962242a6be8ac206f21e5d27ea8b1f212bd))
* use action URLs for tracking endpoints to bypass CDN caching ([67fb674](https://github.com/LindemannRock/craft-smartlink-manager/commit/67fb674273cd8649e817fb45a20ba7d4e765bac4))
* use action URLs for tracking endpoints to bypass CDN caching ([44ba917](https://github.com/LindemannRock/craft-smartlink-manager/commit/44ba917e05622ac04902e6ac4426bccbf675e207))
* use array_key_exists for attribute checks in settings configuration ([31e8b40](https://github.com/LindemannRock/craft-smartlink-manager/commit/31e8b40191b9c7f1d689e86e97a10f26f401a347))
* wait for tracking to complete before redirect ([4400b5e](https://github.com/LindemannRock/craft-smartlink-manager/commit/4400b5e7196541cb65ceaa86b40bbc570594be60))


### Miscellaneous Chores

* **main:** release 1.0.1 ([294ae46](https://github.com/LindemannRock/craft-smartlink-manager/commit/294ae468ee6b64da59a31f57a0e0f572c6ced2f3))
* **main:** release 1.0.1 ([9299d1f](https://github.com/LindemannRock/craft-smartlink-manager/commit/9299d1f2373367d9de5c484e1874c6f4d3a77076))
* **main:** release 1.0.2 ([7698cc1](https://github.com/LindemannRock/craft-smartlink-manager/commit/7698cc1f5db443f55e204739cf02145a04d5c56e))
* **main:** release 1.0.2 ([44a53cb](https://github.com/LindemannRock/craft-smartlink-manager/commit/44a53cbe9fc5e56b938294837e08ef425816faa4))
* **main:** release 1.0.3 ([84b001d](https://github.com/LindemannRock/craft-smartlink-manager/commit/84b001df4b9e3286e6e054a6a879cd7c9cd6c0b4))
* **main:** release 1.0.3 ([e9bb3d7](https://github.com/LindemannRock/craft-smartlink-manager/commit/e9bb3d7c7e255cacb98034bc13bdb4b5bf59df06))
* **main:** release 1.0.4 ([4f9d3d4](https://github.com/LindemannRock/craft-smartlink-manager/commit/4f9d3d4f8bb94c35e0977b2db867014887b974ed))
* **main:** release 1.0.4 ([4152201](https://github.com/LindemannRock/craft-smartlink-manager/commit/415220154477e18f4c3520099f56e2f1896fc0ff))
* **main:** release 1.1.0 ([36ec264](https://github.com/LindemannRock/craft-smartlink-manager/commit/36ec26487148541cdd81f3f7fbe5209ff8864200))
* **main:** release 1.1.0 ([907bfc8](https://github.com/LindemannRock/craft-smartlink-manager/commit/907bfc8df2257952af0871df00978895a8075f2e))
* **main:** release 1.10.0 ([48b4cb4](https://github.com/LindemannRock/craft-smartlink-manager/commit/48b4cb498e4ad66c568b4a0882f4c2ec997e8e82))
* **main:** release 1.10.0 ([7fd4dab](https://github.com/LindemannRock/craft-smartlink-manager/commit/7fd4dab5128b9b1eff869a483adc4a103ed7f718))
* **main:** release 1.11.0 ([4233d87](https://github.com/LindemannRock/craft-smartlink-manager/commit/4233d87e96e02cd4d37c64a7db0ee3dee8a4da28))
* **main:** release 1.11.0 ([3323a4c](https://github.com/LindemannRock/craft-smartlink-manager/commit/3323a4c57f77e038edae7b2bfd221931c3d99df7))
* **main:** release 1.12.0 ([0d75f44](https://github.com/LindemannRock/craft-smartlink-manager/commit/0d75f44e89b7a3948193d6aab9b178368624b1d2))
* **main:** release 1.12.0 ([49108e6](https://github.com/LindemannRock/craft-smartlink-manager/commit/49108e6c53cf9500c7d031fd0a5321634307517a))
* **main:** release 1.13.0 ([1f3fa72](https://github.com/LindemannRock/craft-smartlink-manager/commit/1f3fa72591d48d84f49490d3024aff0bf1f12036))
* **main:** release 1.13.0 ([4eeff48](https://github.com/LindemannRock/craft-smartlink-manager/commit/4eeff48d0aab2fa264c231833b49f4d8b2a4d503))
* **main:** release 1.13.1 ([0da4182](https://github.com/LindemannRock/craft-smartlink-manager/commit/0da4182d892a93c1160dd3f8d35eb6de9d9cb28c))
* **main:** release 1.13.1 ([2d41b6d](https://github.com/LindemannRock/craft-smartlink-manager/commit/2d41b6d24d404381ce6ab71fcb116127ac04a9d2))
* **main:** release 1.13.2 ([e124691](https://github.com/LindemannRock/craft-smartlink-manager/commit/e12469170ae5d01928ac00c1b075580a76889e66))
* **main:** release 1.13.2 ([edbeb22](https://github.com/LindemannRock/craft-smartlink-manager/commit/edbeb22eee9661710a7f02e173980dbce31c2261))
* **main:** release 1.13.3 ([aa3f90b](https://github.com/LindemannRock/craft-smartlink-manager/commit/aa3f90b235292f3173b516a0b3e7d06f21052df1))
* **main:** release 1.13.3 ([29974bd](https://github.com/LindemannRock/craft-smartlink-manager/commit/29974bd29bf2abff9d02be8e529ec433100f2222))
* **main:** release 1.13.4 ([bb5e398](https://github.com/LindemannRock/craft-smartlink-manager/commit/bb5e398ebb09b46952f8070b267604fd67c1a116))
* **main:** release 1.13.4 ([6e2dfb6](https://github.com/LindemannRock/craft-smartlink-manager/commit/6e2dfb69148f756065f1c6fbaaaeee3d7dc948c0))
* **main:** release 1.13.5 ([a966b73](https://github.com/LindemannRock/craft-smartlink-manager/commit/a966b7340446430f43b2b999afab67ab7f35c0a2))
* **main:** release 1.13.5 ([9efea73](https://github.com/LindemannRock/craft-smartlink-manager/commit/9efea733d4b0009e7f08983d8866465247bed9cd))
* **main:** release 1.13.6 ([d9b2dc1](https://github.com/LindemannRock/craft-smartlink-manager/commit/d9b2dc151d30ee7ba80cf61106247092913438a5))
* **main:** release 1.13.6 ([3fe3b4d](https://github.com/LindemannRock/craft-smartlink-manager/commit/3fe3b4daf4261aa89da31b944c1b15172320a915))
* **main:** release 1.13.7 ([b910c04](https://github.com/LindemannRock/craft-smartlink-manager/commit/b910c041379e49ac4766402e274686e5be48cd0e))
* **main:** release 1.13.7 ([a5b2056](https://github.com/LindemannRock/craft-smartlink-manager/commit/a5b2056e630d374209c259f2740819a013df2f6e))
* **main:** release 1.14.0 ([2991ea6](https://github.com/LindemannRock/craft-smartlink-manager/commit/2991ea65bfe523625c6f85b5068cf1a28c7a6bf8))
* **main:** release 1.14.0 ([589895a](https://github.com/LindemannRock/craft-smartlink-manager/commit/589895a107e355f716706c1c44d86a2eada6b8e9))
* **main:** release 1.15.0 ([74a6b32](https://github.com/LindemannRock/craft-smartlink-manager/commit/74a6b32dddb535f1f30e5d4ffe07471411154a2d))
* **main:** release 1.15.0 ([54507de](https://github.com/LindemannRock/craft-smartlink-manager/commit/54507de8b39d633624fb456451b2a3d381984cc6))
* **main:** release 1.16.0 ([1c924d1](https://github.com/LindemannRock/craft-smartlink-manager/commit/1c924d19e238129c0aed561a3d70fc7996cc3bd5))
* **main:** release 1.16.0 ([eac4aff](https://github.com/LindemannRock/craft-smartlink-manager/commit/eac4affe3c196b79afe57e439b4f9bfeaa2e49e3))
* **main:** release 1.17.0 ([324aa36](https://github.com/LindemannRock/craft-smartlink-manager/commit/324aa36d79f49664e7ea3d3a75d5508bd21ca2d9))
* **main:** release 1.17.0 ([ddde5ac](https://github.com/LindemannRock/craft-smartlink-manager/commit/ddde5ace8e7020aa85ae255c157941726b4a7153))
* **main:** release 1.17.1 ([67a60e3](https://github.com/LindemannRock/craft-smartlink-manager/commit/67a60e373ac619bad24e06c2e78a1e373112f14e))
* **main:** release 1.17.1 ([dd39b37](https://github.com/LindemannRock/craft-smartlink-manager/commit/dd39b374c2b34c0c162136228a32d75a4321d0be))
* **main:** release 1.17.2 ([ccb27f5](https://github.com/LindemannRock/craft-smartlink-manager/commit/ccb27f5e5cfe53ad968a8d439ece24812032db57))
* **main:** release 1.17.2 ([5ad99b1](https://github.com/LindemannRock/craft-smartlink-manager/commit/5ad99b1fae773de22607b4a70ac347481da98cc6))
* **main:** release 1.18.0 ([26e14d3](https://github.com/LindemannRock/craft-smartlink-manager/commit/26e14d39ed9442ddf9501d4f9aca175cf43cdf76))
* **main:** release 1.18.0 ([a26a6ba](https://github.com/LindemannRock/craft-smartlink-manager/commit/a26a6baef33dd7dfe88eb5426d8f5cf2ac0cf39d))
* **main:** release 1.19.0 ([f48d24a](https://github.com/LindemannRock/craft-smartlink-manager/commit/f48d24a9dfb9f2df59441c348c438b4e8d755340))
* **main:** release 1.19.0 ([d740cc7](https://github.com/LindemannRock/craft-smartlink-manager/commit/d740cc78e5821fb9a3a4ab0cfb79458e67388890))
* **main:** release 1.2.0 ([5ab969b](https://github.com/LindemannRock/craft-smartlink-manager/commit/5ab969b09a24f9ed2d534211227465eaba12536a))
* **main:** release 1.2.0 ([9a71da0](https://github.com/LindemannRock/craft-smartlink-manager/commit/9a71da0b3f1079a5421ce36b2282efa1c7a959c3))
* **main:** release 1.2.1 ([160cab5](https://github.com/LindemannRock/craft-smartlink-manager/commit/160cab500248ca727a8bf5df52eeafed4fb23858))
* **main:** release 1.2.1 ([8c08a56](https://github.com/LindemannRock/craft-smartlink-manager/commit/8c08a565b6d43190490d79be65ba69c8ba030bc2))
* **main:** release 1.2.2 ([665d1bf](https://github.com/LindemannRock/craft-smartlink-manager/commit/665d1bf7efec85cba32fdf9efec9e7c3fcd1df7e))
* **main:** release 1.2.2 ([bc6edd6](https://github.com/LindemannRock/craft-smartlink-manager/commit/bc6edd6b93ed78c9002791c5041dd84f1ec8339b))
* **main:** release 1.3.0 ([05f7bd0](https://github.com/LindemannRock/craft-smartlink-manager/commit/05f7bd0346b2d86db88aa592f972b055e74a0401))
* **main:** release 1.3.0 ([c2e627f](https://github.com/LindemannRock/craft-smartlink-manager/commit/c2e627fe2729fec4afcd2626721c810ff2362844))
* **main:** release 1.4.0 ([5c6aaad](https://github.com/LindemannRock/craft-smartlink-manager/commit/5c6aaad5a83c2e3e2ea0e2340a306782d9ed1039))
* **main:** release 1.4.0 ([faafc82](https://github.com/LindemannRock/craft-smartlink-manager/commit/faafc82f62bcf989ff35f19c4ab338ca646f266d))
* **main:** release 1.4.1 ([c3aacb9](https://github.com/LindemannRock/craft-smartlink-manager/commit/c3aacb94caed580a8debd0f9c3446f40ee8f20c6))
* **main:** release 1.4.1 ([c46249d](https://github.com/LindemannRock/craft-smartlink-manager/commit/c46249dd34971b47900f18ddb917f878d8d0496d))
* **main:** release 1.4.2 ([1fa1a6a](https://github.com/LindemannRock/craft-smartlink-manager/commit/1fa1a6a90c2a97f1ebf329495f62cb86a143b4f0))
* **main:** release 1.4.2 ([3091864](https://github.com/LindemannRock/craft-smartlink-manager/commit/3091864ed0c199c18e8b6ef4bdcf4d992f885438))
* **main:** release 1.5.0 ([c9951aa](https://github.com/LindemannRock/craft-smartlink-manager/commit/c9951aa43bcae7a3ff5fec52df712e4a547a8b8c))
* **main:** release 1.5.0 ([2b877b6](https://github.com/LindemannRock/craft-smartlink-manager/commit/2b877b61036631af2e9231c22bd96833a0e72120))
* **main:** release 1.6.0 ([e43f2bf](https://github.com/LindemannRock/craft-smartlink-manager/commit/e43f2bffb4e48e051c61e66169cedb9d5d548b19))
* **main:** release 1.6.0 ([10a835d](https://github.com/LindemannRock/craft-smartlink-manager/commit/10a835d22e014c0e5c9364464389eda34e180657))
* **main:** release 1.7.0 ([a31f28d](https://github.com/LindemannRock/craft-smartlink-manager/commit/a31f28dbe85a38df924556a6a9be5065dc3646ad))
* **main:** release 1.7.0 ([5978949](https://github.com/LindemannRock/craft-smartlink-manager/commit/5978949f053a6e5fc7accc9aae96771481eeb9d7))
* **main:** release 1.7.1 ([0bc5e61](https://github.com/LindemannRock/craft-smartlink-manager/commit/0bc5e61a9739fa19acdb377518cafd1f0260d7c1))
* **main:** release 1.7.1 ([6e3b38e](https://github.com/LindemannRock/craft-smartlink-manager/commit/6e3b38e3fb67195ff597110772b31a3e9534a457))
* **main:** release 1.8.0 ([af64789](https://github.com/LindemannRock/craft-smartlink-manager/commit/af647893ae30445d04b423b81d6d057e5e8fd612))
* **main:** release 1.8.0 ([ab8c894](https://github.com/LindemannRock/craft-smartlink-manager/commit/ab8c894241ede35c84ca061c1b48aec9568f682e))
* **main:** release 1.9.0 ([49279a9](https://github.com/LindemannRock/craft-smartlink-manager/commit/49279a92b0ba86820adcbadb014d913f90d8872a))
* **main:** release 1.9.0 ([9b0b7cc](https://github.com/LindemannRock/craft-smartlink-manager/commit/9b0b7cc1216c8558838237c1d82eb74d34dbfd23))
* **main:** release 1.9.1 ([daf8f8b](https://github.com/LindemannRock/craft-smartlink-manager/commit/daf8f8b885e943f6ca8f35b805d017dd2e7ef51a))
* **main:** release 1.9.1 ([47d7b9f](https://github.com/LindemannRock/craft-smartlink-manager/commit/47d7b9f540d10d75d3d703a7b19bafef7f4720df))
* **main:** release 1.9.2 ([33331fe](https://github.com/LindemannRock/craft-smartlink-manager/commit/33331feda5b84294e28e30ec9c6a1876fa05aa0a))
* **main:** release 1.9.2 ([310abf8](https://github.com/LindemannRock/craft-smartlink-manager/commit/310abf8a302c6d96cbd0bbd73667bf675ec8534e))
* release 1.19.1 ([c1fc18e](https://github.com/LindemannRock/craft-smartlink-manager/commit/c1fc18e529c115fd52c4e465b1cb35d06c9fe2e4))

## [1.19.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.18.0...v1.19.0) (2025-10-02)


### Features

* Add interaction type breakdown to Performance card ([9c47423](https://github.com/LindemannRock/craft-smartlink-manager/commit/9c47423dc73360e24710ff79fcf001badf0d5de9))
* remove redundant enabled and clicks columns from smartlinks table ([ec79d43](https://github.com/LindemannRock/craft-smartlink-manager/commit/ec79d43e4a0b28e4415150a3d6297cdbbe4c069e))


### Bug Fixes

* enabled status requiring two saves to work ([19a7723](https://github.com/LindemannRock/craft-smartlink-manager/commit/19a77233e4ce6add702e903c453217b4d0392fd5))
* enabled status requiring two saves to work ([1106a02](https://github.com/LindemannRock/craft-smartlink-manager/commit/1106a028603c6c0886800ad78cc0822ba66f3b2f))

## [1.19.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.18.0...v1.19.0) (2025-10-01)


### Features

* Add interaction type breakdown to Performance card ([9c47423](https://github.com/LindemannRock/craft-smartlink-manager/commit/9c47423dc73360e24710ff79fcf001badf0d5de9))
* remove redundant enabled and clicks columns from smartlinks table ([ec79d43](https://github.com/LindemannRock/craft-smartlink-manager/commit/ec79d43e4a0b28e4415150a3d6297cdbbe4c069e))

## [1.18.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.17.2...v1.18.0) (2025-10-01)


### Features

* Improve analytics data management and platform display ([d60def7](https://github.com/LindemannRock/craft-smartlink-manager/commit/d60def7a5fa4014e6ed9a201e251bedc28879c4f))


### Bug Fixes

* multi-site analytics tracking ([493bbc4](https://github.com/LindemannRock/craft-smartlink-manager/commit/493bbc427bca5b0ba4e4575f88c4d98ef1405ac9))
* Update URL assignment to check both redirectUrl and buttonUrl formats ([832f196](https://github.com/LindemannRock/craft-smartlink-manager/commit/832f1962242a6be8ac206f21e5d27ea8b1f212bd))

## [1.17.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.17.1...v1.17.2) (2025-10-01)


### Bug Fixes

* Show read-only notice only on Field Layout settings page ([049d7ca](https://github.com/LindemannRock/craft-smartlink-manager/commit/049d7ca021d451ccf7756a1e06af4c4b73949924))

## [1.17.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.17.0...v1.17.1) (2025-10-01)


### Bug Fixes

* Preserve QR source parameter and display destination URLs in analytics ([a579481](https://github.com/LindemannRock/craft-smartlink-manager/commit/a579481cb2fc65772a47fc380405b8c106527f78))

## [1.17.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.16.0...v1.17.0) (2025-10-01)


### Features

* Add read-only mode for Smart Links settings when allowAdminChanges is disabled ([a9ad703](https://github.com/LindemannRock/craft-smartlink-manager/commit/a9ad70344ceaf8b304b848de739600a2d0d00e90))

## [1.16.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.15.0...v1.16.0) (2025-10-01)


### Features

* Add field layout support with project config sync ([21e0ba8](https://github.com/LindemannRock/craft-smartlink-manager/commit/21e0ba8551bd5a7c58e603e93db3651cadcac3cc))

## [1.15.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.14.0...v1.15.0) (2025-10-01)


### Features

* Register project config event handlers and save field layout UID ([3490026](https://github.com/LindemannRock/craft-smartlink-manager/commit/34900265fa6609fd8fbc092d67fa53100dab01dc))

## [1.14.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.13.7...v1.14.0) (2025-10-01)


### Features

* Add Field Layout support to Smart Links element type ([7b77015](https://github.com/LindemannRock/craft-smartlink-manager/commit/7b77015311250dd08af76b7069c8bb3e0d8377eb))

## [1.13.7](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.13.6...v1.13.7) (2025-10-01)


### Bug Fixes

* smart link tracking to work with static page caching ([1fb2774](https://github.com/LindemannRock/craft-smartlink-manager/commit/1fb2774df9761bd8b8c5c7aaad9cf925ed969add))

## [1.13.6](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.13.5...v1.13.6) (2025-09-30)


### Bug Fixes

* wait for tracking to complete before redirect ([4400b5e](https://github.com/LindemannRock/craft-smartlink-manager/commit/4400b5e7196541cb65ceaa86b40bbc570594be60))

## [1.13.5](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.13.4...v1.13.5) (2025-09-30)


### Bug Fixes

* replace sendBeacon with fetch POST for CDN compatibility ([71a62dd](https://github.com/LindemannRock/craft-smartlink-manager/commit/71a62dd917f1a1d8ec8d4b9bf97b8ac11708af59))

## [1.13.4](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.13.3...v1.13.4) (2025-09-30)


### Bug Fixes

* use action URLs for tracking endpoints to bypass CDN caching ([67fb674](https://github.com/LindemannRock/craft-smartlink-manager/commit/67fb674273cd8649e817fb45a20ba7d4e765bac4))

## [1.13.3](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.13.2...v1.13.3) (2025-09-30)


### Bug Fixes

* use action URLs for tracking endpoints to bypass CDN caching ([44ba917](https://github.com/LindemannRock/craft-smartlink-manager/commit/44ba917e05622ac04902e6ac4426bccbf675e207))

## [1.13.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.13.1...v1.13.2) (2025-09-30)


### Bug Fixes

* make redirects truly cache-safe by moving URL selection to client-side ([bdbfa15](https://github.com/LindemannRock/craft-smartlink-manager/commit/bdbfa15bacdaf5484602b10e623f935420c509d9))

## [1.13.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.13.0...v1.13.1) (2025-09-30)


### Bug Fixes

* improve tracking and analytics display ([d94701c](https://github.com/LindemannRock/craft-smartlink-manager/commit/d94701c5290c2323bc811a7b1acdf0fd5a8a6f48))

## [1.13.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.12.0...v1.13.0) (2025-09-30)


### Features

* refactor analytics tracking to client-side JavaScript for CDN compatibility ([edfd7a9](https://github.com/LindemannRock/craft-smartlink-manager/commit/edfd7a91bccb7bacc0caeba9ea805e59c2b3cf42))

## [1.12.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.11.0...v1.12.0) (2025-09-30)


### Features

* add QR code cache busting setting to fix tracking with CDN caching ([72eac94](https://github.com/LindemannRock/craft-smartlink-manager/commit/72eac947123e427262617346103543810347fb4d))

## [1.11.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.10.0...v1.11.0) (2025-09-30)


### Features

* enhance settings UI with URL and template configuration options for smart links and QR codes ([239219d](https://github.com/LindemannRock/craft-smartlink-manager/commit/239219d1c4449067f558148b5bab2d1ca0ae7d88))

## [1.10.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.9.2...v1.10.0) (2025-09-30)


### Features

* add configurable URL prefixes for smart links and QR codes ([f7239b2](https://github.com/LindemannRock/craft-smartlink-manager/commit/f7239b2f47d3e3329c1d0bc4dc181e69eb033b4d))
* add custom QR code template settings and update related translations ([c362642](https://github.com/LindemannRock/craft-smartlink-manager/commit/c362642eb71a064e27da7cbc360225efe100ae3e))
* add customizable URL prefixes and templates for smart links and QR codes ([eff264d](https://github.com/LindemannRock/craft-smartlink-manager/commit/eff264d7cc39d6d81d622f1628978a6d261ef28f))

## [1.9.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.9.1...v1.9.2) (2025-09-30)


### Bug Fixes

* update device detection method in RedirectController ([198fc1a](https://github.com/LindemannRock/craft-smartlink-manager/commit/198fc1acadd5a050052b2c1ca8db9343bfea914e))

## [1.9.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.9.0...v1.9.1) (2025-09-30)


### Bug Fixes

* update device detection method in RedirectController ([3e7fb1a](https://github.com/LindemannRock/craft-smartlink-manager/commit/3e7fb1abcfd76bbbbecd9fb4bfca2706edbf47c9))

## [1.9.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.8.0...v1.9.0) (2025-09-30)


### Features

* update caching strategy in RedirectController to vary by device type ([9bb8e4b](https://github.com/LindemannRock/craft-smartlink-manager/commit/9bb8e4bd881509e72fb5f8f60f2c8d9726ddfbc1))

## [1.8.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.7.1...v1.8.0) (2025-09-30)


### Features

* enhance CSRF token response with device detection information ([5af440b](https://github.com/LindemannRock/craft-smartlink-manager/commit/5af440ba912c25b3e97df877cf8f60de1747af26))

## [1.7.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.7.0...v1.7.1) (2025-09-30)


### Bug Fixes

* update site selection logic in multi-site configuration ([d2bd97b](https://github.com/LindemannRock/craft-smartlink-manager/commit/d2bd97baae4bd6f865f9da53621e581f12e36cca))

## [1.7.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.6.0...v1.7.0) (2025-09-30)


### Features

* add CSRF token refresh for cached pages and fix metadata serialization ([c22c2b1](https://github.com/LindemannRock/craft-smartlink-manager/commit/c22c2b138e3c93382621cb7f1fcaaf9999a4c898))


### Bug Fixes

* update instruction for custom redirect template field ([de0a299](https://github.com/LindemannRock/craft-smartlink-manager/commit/de0a299fd959ff56f2f8a48357e0e3424455548f))
* update PHP requirement from ^8.0.2 to ^8.2 in composer.json ([29d375d](https://github.com/LindemannRock/craft-smartlink-manager/commit/29d375d857f2f3eb9277318c24150ac3034e1120))
* use array_key_exists for attribute checks in settings configuration ([31e8b40](https://github.com/LindemannRock/craft-smartlink-manager/commit/31e8b40191b9c7f1d689e86e97a10f26f401a347))

## [1.6.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.5.0...v1.6.0) (2025-09-25)


### Features

* add Smart Links utility template with link statistics and recent analytics ([acf62c7](https://github.com/LindemannRock/craft-smartlink-manager/commit/acf62c7ad344275381fdce7cfbefa74b8f674591))

## [1.5.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.4.2...v1.5.0) (2025-09-24)


### Features

* enhance settings handling with additional debug logging and auto-setting for qrLogoVolumeUid ([a3b7d71](https://github.com/LindemannRock/craft-smartlink-manager/commit/a3b7d7112493de5c0c56a27c29914bf02c87768a))

## [1.4.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.4.1...v1.4.2) (2025-09-24)


### Bug Fixes

* update repository links in README and composer.json to reflect new naming ([a239296](https://github.com/LindemannRock/craft-smartlink-manager/commit/a239296fbe4e9cc70bd86863bd89fbcec3031043))

## [1.4.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.4.0...v1.4.1) (2025-09-24)


### Bug Fixes

* improve description in CleanupAnalyticsJob and format .gitignore entries ([3a58cbc](https://github.com/LindemannRock/craft-smartlink-manager/commit/3a58cbc9cd5403b2413e9a644ec7b7026baab72f))

## [1.4.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.3.0...v1.4.0) (2025-09-15)


### Features

* update README and migration for site settings in Smart Links ([c309b1b](https://github.com/LindemannRock/craft-smartlink-manager/commit/c309b1b98e1a00b75f09c039b6054c736e0ed1b5))

## [1.3.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.2.2...v1.3.0) (2025-09-15)


### Features

* add checkbox group for enabling Smart Links on specific sites ([a0d6f85](https://github.com/LindemannRock/craft-smartlink-manager/commit/a0d6f8586d7135625128b61857bc50d52abcd46d))
* add enabledSites property to Settings model for site-specific Smart Links configuration ([828b105](https://github.com/LindemannRock/craft-smartlink-manager/commit/828b105f4fc2335edec9227be4b0a81198233e31))
* add multi-site management and site selection configuration for Smart Links ([304ebc1](https://github.com/LindemannRock/craft-smartlink-manager/commit/304ebc1470760ad2e8e7f66d11996358bc81f279))
* add site settings and default settings row to smartlinks_settings table ([c143d41](https://github.com/LindemannRock/craft-smartlink-manager/commit/c143d41a1fb7d5b2cd0c2d8deb254284a5bff4e2))
* implement site-specific Smart Links functionality and enable site selection in templates ([6c87105](https://github.com/LindemannRock/craft-smartlink-manager/commit/6c871052fcfa89f39611b97ed62c4bd2d1a04d60))

## [1.2.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.2.1...v1.2.2) (2025-09-15)


### Bug Fixes

* handle empty QR logo and image IDs in SmartLinksController ([d9a7e65](https://github.com/LindemannRock/craft-smartlink-manager/commit/d9a7e65055ca27534f382ad29aec7a95eeaa10e7))

## [1.2.1](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.2.0...v1.2.1) (2025-09-15)


### Bug Fixes

* update copyright notice in LICENSE file ([3a2531c](https://github.com/LindemannRock/craft-smartlink-manager/commit/3a2531cd2086d5dddc2e7a16905ed3ae6fa35f05))

## [1.2.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.1.0...v1.2.0) (2025-09-14)


### Features

* add plugin credit component to settings and analytics templates ([c22cf96](https://github.com/LindemannRock/craft-smartlink-manager/commit/c22cf96fde791c79b1e650964985cf44f8beeba6))

## [1.1.0](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.0.4...v1.1.0) (2025-09-11)


### Features

* enhance README with additional features for image management and landing page customization ([8162b36](https://github.com/LindemannRock/craft-smartlink-manager/commit/8162b36ffec42db3b9701d2cf6dd96cf92f9617f))


### Bug Fixes

* Smart Links database schema to match working installation ([03fe1dd](https://github.com/LindemannRock/craft-smartlink-manager/commit/03fe1dd45e8985bafe8996f3b38dde2d01740057))

## [1.0.4](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.0.3...v1.0.4) (2025-09-10)


### Bug Fixes

* update requirements in README for clarity and consistency ([a17ca25](https://github.com/LindemannRock/craft-smartlink-manager/commit/a17ca2501f162c2c60df0f82449f142f5337d7e3))

## [1.0.3](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.0.2...v1.0.3) (2025-09-10)


### Bug Fixes

* update displayName method to return plugin name and rename iconPath to icon ([aca60a0](https://github.com/LindemannRock/craft-smartlink-manager/commit/aca60a06bc689820a2d407270541e1c4222d5853))

## [1.0.2](https://github.com/LindemannRock/craft-smartlink-manager/compare/v1.0.1...v1.0.2) (2025-09-02)


### Bug Fixes

* remove development backups and IDE files ([f078fdb](https://github.com/LindemannRock/craft-smartlink-manager/commit/f078fdb024b40398b2ad93c9d9499ffc9172a021))

## 1.0.1 (2025-09-02)


### Features

* initial Smart Links plugin implementation ([6b5c0ed](https://github.com/LindemannRock/craft-smartlink-manager/commit/6b5c0ed5911f8ecdb803cb0c76395fdce7bb03ef))
