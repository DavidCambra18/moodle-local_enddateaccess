# Course End Date Access (local_enddateaccess)

## Description
This local plugin automates the process of restricting access to course modules based on the course end date. It listens to course update events and automatically applies a "prevent access after course end date" availability restriction to all modules that are tracked for course completion.

Instead of manually editing the deadline for every single activity when a course schedule changes, administrators and teachers can simply update the course end date. The plugin will instantly sync the deadline across all relevant activities.

## Key Features
* **Automatic Synchronization:** Instantly updates module availability restrictions when the course end date is modified.
* **Safe Merging (Non-destructive):** The plugin intelligently parses the existing availability JSON. It safely adds or updates the date restriction without overwriting or destroying existing conditions (e.g., group restrictions, grade requirements, or user profiles).
* **Targeted Action:** Only applies restrictions to activities that have course completion criteria enabled, leaving optional resources untouched.
* **Cleanup:** If the completion tracking is removed from a module, the plugin automatically removes the injected date restriction while keeping any other conditions intact.

## Installation
1. Download the plugin and extract the `.zip` file.
2. Rename the extracted folder to `enddateaccess` (if it isn't already).
3. Upload or move the `enddateaccess` folder into the `local/` directory of your Moodle installation.
4. Log in as an administrator and go to **Site administration > Notifications** (or run the upgrade script via CLI) to complete the installation process.

## Usage
The plugin works silently in the background. To trigger it:
1. Ensure your course has an **End date** set in the course settings.
2. Add an activity (like a Quiz or Assignment) and configure its **Activity completion** settings to require a specific condition (e.g., "Student must view this activity to complete it" or "Student must receive a grade").
3. Save the activity. The plugin will automatically append the date restriction based on the course end date.

## Requirements
* Moodle 3.5 or later (Requires version `2018051700`).

## License
This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.