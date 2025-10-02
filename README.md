![Development stage: stable](https://img.shields.io/badge/development%20stage-stable-blue)
[![Software License](https://img.shields.io/badge/license-AGPL-brightgreen.svg)](LICENSE)

# Team Folder Tags

This is a Nextcloud app, that allows you to attach key-value tags to team folders (formerly called groupfolders) using a PHP OCA API or occ commands.

## About

Without this app if you wanted to automate/script the creation and updates of team folders you couldn't attach metadata to that team folder to recognize it in your script in the future.
You had to create your own database to map from your own unique key to the team folder id or encode that information in the user-visible team folder name.

With this app you can add key-value tags to your team folders (only visible to admins) making it trivially easy to recognize them again in your automations.

This was created for the organization_folders groupfolder management app, but was kept generic to allow it to be used by your custom automations.

**This app does not provide a frontend, it is designed to be used in conjunction with other apps/scripts.**
