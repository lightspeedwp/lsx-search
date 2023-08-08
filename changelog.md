# Change log

# [[1.5.7]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.5.7) - 2023-08-09

### Security
- General testing to ensure compatibility with latest WordPress version (6.3).

# [[1.5.6]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.5.6) - 2023-04-20

### Security
- General testing to ensure compatibility with latest WordPress version (6.2).

# [[1.5.5]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.5.5) - 2022-12-22

### Added
- Added in the Price sorting for accommodation and tour post types.

### Fixed

- Fixing the integration issues with TO and SearchWP 

### Updated
- Updating the pretty search URL for use with searchWP

### Security
- General testing to ensure compatibility with latest WordPress version (6.1.1).
- General testing to ensure compatibility with latest FacetWP version (4.1.3).
- General testing to ensure compatibility with latest SearchWP version (4.2.8).

# [[1.5.4]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.5.4) - 2022-05-26

### Security
- General testing to ensure compatibility with latest WordPress version (6.0)

### Added
- Added the 'sort' and 'autocomplete' to the list of excluded facets from the sidebar.
- Allowing the new sorter to be used in the Search Top bar if created and selected in the settings.

### Fixed
- Updating the FacetWP search functions to 3.9.3 causing JS errors.

### Updated
- Removed the wc_header_banner function, move to the LSX theme.

# [[1.5.3]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.5.3) - 2021-04-09

### Fixed

- Restricted the layout switcher filter to work only on the search results pages.

# [[1.5.2]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.5.2) - 2021-03-17

### Added

- Adding in backwards compatibility for the LSX search get_query_var

### Fixed

- The loading overlay pushing the facets down.

# [[1.5.1]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.5.1) - 2021-01-15

### Fixed
- Fixing the search query using a supplemental engine.
- Fixing the banner titles not using the correct values.
- Fixing the url the form "action" attribute points to.

### Updated
- Documentation and support links.

### Security
- General testing to ensure compatibility with latest WordPress version (5.6).

# [[1.5.0]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.5.0) - 2020-11-04

## Added

- Updating the global search filters.
- Updating search settings.

## Fixed

- Fixed spacing for the archive layouts.
- Fixed the sorting option not working.
- Excluded the search filters from the Product Vendors taxonomy for WooCommerce.

## Deprecated

- Removing the CMB and UIX library.

## Security

- Updating dependencies to prevent vulnerabilities.
- Updating PHPCS options for better code.
- General testing to ensure compatibility with latest WordPress version (5.5).
- General testing to ensure compatibility with latest LSX Theme version (2.9).

## [[1.4.1]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.4.1) - 2020-05-21

### Added

- If collapsible facets are on, the first facet will be open by default.
- Added a hover state for the 'sort by' selects.
- Added compatibility for the [pager] facetwp.

### Security

- Updating dependencies to prevent vulnerabilities.
- General testing to ensure compatibility with latest WordPress version (5.4.1).
- General testing to ensure compatibility with latest LSX Theme version (2.8).

## [[1.4.0]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.4.0) - 2020-03-30

### Added

- Post tag format improved.
- Merged the 2 loops outputting the facets, to allow the order from the settings to be maintained.

### Changed

- Changed the search slug to /search/ for the JSON+LD output in Yoast SEO.

### Fixed

- Fixed PHP error `is_search was called incorrectly`.
- Fixed PHP error `Undefined index: products_search_display_clear_button`.
- Updated the search facet output to check for the type and not the name.
- Fixing the Yoda Conditions.
- Fixed the button colours not changing with the lsx customizer plugin.

### Deprecated

- Removed the 66% width restriction on the `<div id="primary">`.

### Security

- Updating dependencies to prevent vulnerabilities.
- General testing to ensure compatibility with latest WordPress version (5.4).
- General testing to ensure compatibility with latest LSX Theme version (2.7).

## [[1.3.3]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.3.3) - 2019-12-19

### Added

- Adding additional classname to archive header for better compatibility with LSX.
- General testing to ensure compatibility with latest WordPress version (5.3).

## [[1.3.2]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.3.2) - 2019-11-13

### Fixed

- Added in a filter to skip the lsx-search.js and lsx-search.min.js files from being deferred.
- Fixed the `Undefined index: _enable_` error.
- Fixed `Undefined index: tribe_events` error.

## [[1.3.1]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.3.1) - 2019-09-30

### Added

- Removing sorting from the bottom page.

### Fixed

- Fix styling for woocommerce search filters.
- Removing PHP errors and console logs.

## [[1.3.0]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.3) - 2019-09-13

### Added

- Cosmetic improvements to Search.
- Adding collapsible facets option.
- Adding the .gitattributes file to remove unnecessary files from the WordPress version.

## [[1.2.1]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.2.1) - 2019-08-06

### Added

- Updating templates with prettier links.
- Grid layout improvements to match the LSX Blog Customizer new design.

### Fixed

- Fixed the pretty search permalinks.
- Spacing styles fixes.
- Removing dashes from post type label.

## [[1.2.0]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.2.0) - 2019-06-19

### Added

- Added in a filter for the FacetWP Checkbox, to customize the hierarchy layout.
- Included a do_action( 'lsx_framework_display_tab_headings_bottom', 'display' ) for the Theme Options display tab.
- Added in 5 filters for the Top of the Search Page (lsx_search_top_show_pagination, lsx_search_top_pagination_visible, lsx_search_top_show_per_page_combo, lsx_search_top_show_sort_combo, lsx_search_top_az_pagination ).
- Added in a do action to the FacetWP Top bar above the search results (lsx_search_facetwp_top_row).
- Added in a filter to allow you to add additional classes to the top FacetWP row.
- Changed the onclick function for the FacetWP Clear Button.
- Added in a filter to allow you to change the clear button function that runs. "lsx_search_clear_function"
- Adding in a settings tab for posts settings.
- Changed the sidebar layout and added in the Sort By and Page By facet options to the bottom bar.
- Added in a `lsx-search-enabled` body class which shows when a page has facets.

### Fixed

- Fixed the undefined error in the mobile JS.

## [[1.0.9]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.2.0) - 2019-01-10

### Added

- Added in 4 filters for the sidebar ( lsx_search_sidebar_before, lsx_search_sidebar_top, lsx_search_sidebar_bottom, lsx_search_sidebar_after ).
- Adding in a filter to allowing overwriting the search prefix lsx_search_prefix.
- Adding in the missing Clear Button option for the search facets.
- Added in a filter to allow changes to the options.

### Fixed

- Updated the uix-core.js to remove the Cyclic error when saving the theme options
- Removed the conditional statement adding the facets to the blog and homepage automatically. (this can be done via the filter).

## [[1.0.8]](https://github.com/lightspeeddevelopment/lsx-search/releases/tag/1.0.8) - 2018-04-26

### Added

- Added in the LSX Search Shortcode.
- Updated the search dropdowns with the Bootstrap 4 classes.
- Added in pll_translate_string to allow translating of the FacetWP Display Value.
- Added in a filter to allow the filtering of the facet display value.
- Added in a filter to allow the overwriting of the facet label.

## [[1.0.7]]()

### Fixed

- Travis Fixes.

### Security

- Security Updates.
- WordPress Coding Standards.

## [[1.0.1]]()

### Added

- Added in a clear link which displays next to the result count if enabled.
- Split up the Frontend class into a Frontend and FacetWP class.
- Changed the way the classes are initiated.
- Added in a filter to call the price including tax if it qualifies.
- Removed the API License Class

## [[1.0.0]]()

### Added

- First Version
