# Course End Date Access (local_enddateaccess)

![Status](https://img.shields.io/badge/Status-Estable-success?style=for-the-badge)
![Version](https://img.shields.io/github/v/release/DavidCambra18/moodle-local_enddateaccess?include_prereleases&style=for-the-badge&color=blue)
[![License](https://img.shields.io/badge/License-GPL%20v3-green?style=for-the-badge)](https://www.gnu.org/licenses/gpl-3.0)

[![Moodle Plugin CI](https://github.com/DavidCambra18/moodle-local_enddateaccess/actions/workflows/ci.yml/badge.svg)](https://github.com/DavidCambra18/moodle-local_enddateaccess/actions/workflows/ci.yml)
[![Moodle.org](https://img.shields.io/badge/Moodle.org-Plugin_Directory-F98012?logo=moodle)](https://moodle.org/plugins/local_enddateaccess)
[![Moodle Versions](https://img.shields.io/badge/Moodle-3.5%20to%204.4-orange.svg)](https://moodle.org/plugins/local_enddateaccess)

## 📖 Description

This local plugin automates the process of restricting access to course modules based on the course end date. It listens to course update events and automatically applies a "prevent access after course end date" availability restriction to all modules that are tracked for course completion.

Instead of manually editing the deadline for every single activity when a course schedule changes, administrators and teachers can simply update the course end date. The plugin will instantly sync the deadline across all relevant activities.

## ✨ Key Features

* **Automatic Synchronization:** Instantly updates module availability restrictions when the course end date is modified.
* **Safe Merging (Non-destructive):** The plugin intelligently parses the existing availability JSON. It safely adds or updates the date restriction without overwriting or destroying existing conditions (e.g., group restrictions, grade requirements, or user profiles).
* **Targeted Action:** Only applies restrictions to activities that have course completion criteria enabled, leaving optional resources untouched.
* **Cleanup:** If the completion tracking is removed from a module, the plugin automatically removes the injected date restriction while keeping any other conditions intact.
* **Global Toggle:** Administrators can easily enable or disable the automatic synchronization globally via the plugin settings without uninstalling it.
* **Audit Logging:** Triggers custom Moodle log events whenever a module restriction is automatically updated, ensuring full traceability for site administrators.

## 🧠 How the Logic Works (Combined Dates)

The plugin is designed to work seamlessly with manual restrictions using an `AND` logic approach:
* **If an activity has a manual "From" date (e.g., June 20th):** The plugin appends the course end date (e.g., June 30th). The student will have access *from June 20th to June 30th*.
* **If an activity has a manual "Until" date (e.g., June 20th):** The strictest condition applies. Since Moodle requires all conditions to be met, the manual restriction blocks access on June 20th, before the course end date (June 30th) is reached.

## 🚀 Installation

1. Download the plugin and extract the `.zip` file.
2. Rename the extracted folder to `enddateaccess` (if it isn't already).
3. Upload or move the `enddateaccess` folder into the `local/` directory of your Moodle installation.
4. Log in as an administrator and go to **Site administration > Notifications** (or run the upgrade script via CLI) to complete the installation process.

## ⚙️ Configuration & Usage

Once installed, the plugin works silently in the background.

**Global Settings:**
Go to **Site administration > Plugins > Local plugins > Course End Date Restriction**. Here you can toggle the **Enable automatic synchronization** setting.

**Triggering the Sync:**
1. Ensure your course has an **End date** set in the course settings.
2. Go to **Course administration > Course completion** and select the activities that are required to complete the course.
3. Save the changes and trigger a course update (by saving the course settings). 
4. The plugin will automatically append the date restriction in the background. Check the course or system logs to see the execution details.

## 🐛 Reporting Bugs and Feature Requests

We use GitHub Issue Templates to keep track of bugs and enhancements. If you find a bug or have an idea for a new feature, please go to the **Issues** tab of this repository and click **New issue**. Select the appropriate template and fill out the required information (like your Moodle and PHP versions) so we can help you faster.

## 🛠️ Requirements

* Moodle 3.5 or later (Requires version `2018051700`).
* PHP 7.2 or higher.

## 🤝 Support the Project

If you find this plugin useful for your Moodle site, please consider giving it a ⭐ **Star** on this repository. It helps the project grow and reach more administrators!

## 📜 License

This plugin is free software: you can redistribute it and/or modify it under the terms of the **GNU General Public License** as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.