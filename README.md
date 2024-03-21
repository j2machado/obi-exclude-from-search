# Obi Exclude from Search

**The problem:** many plugins register custom post types on your website for their data flow. Some of those post types are publicly accessible. In some cases, you don't want your users accessing that data from the WordPress search. Let's say, that is not the way you intend users accessing the data on your site.

**The solution:** exclude some post types from the WordPress search.

This plugin does just that. The current stable version now is working.

**A demo image:**

## BEFORE:

![Alt Obi Exclude from Search admin options screenshot](https://obijuan.dev/wp-content/uploads/2023/06/obi-remove-post-types-from-search.png)

## AFTER:

![Alt Obi Exclude from Search new admin options](https://www.excludefromsearch.com/wp-content/uploads/2024/03/obi-exclude-from-search-new-admin.png)

Behind the scenes, we retrieve the available public post types in the website and store them in an option with their current status whether search-enabled or not.  

If a plugin that registers custom post types is removed, then those specific post types are removed from the option.  

If a new plugin later in time is added which adds public custom post types, these are added to the options with their respective statuses.  
---
Post types that are search-enabled, will have their respective checkbox as 'checked'. **(THIS IS TO GOING TO BE CHANGED TO THE OPPOSITE BEHAVIOR)**.

If there is a post type that is unchecked, and you want to include it in the WordPress search, simply check the checkbox and save the changes.
---

## TODO:

- Add a {plugin_name} - {post_type} structure in the 'Exclude full post types' list.
- Refactor the adjust_search_query logic. Instead of including posts or post types, we are geared towards excluding them.
- Refactor the options selection logic in the admin UI. Instead of checking the included post types, we should check the post types to be excluded and keep the ones included unchecked.
- Add a general exclusion rule by user role. E.g., exclude admins from the plugin's rules (admins will be unaffected by the plugins logic).
- Add a general exclusion rule by login status. E.g., exclude logged-in or logged-out visitors from the plugin's rules.
- Add an Exclude from Search option on each individual post edit page.
- Add an Exclude from Search bulk edit capability for posts in any (custom) post types.
- Add an integrations engine. Register integrations to extend Exclude from Search to content heavy plugins from third-parties.