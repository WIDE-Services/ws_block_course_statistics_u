# block_course_statistics

## Overview

The `block_course_statistics` plugin for Moodle provides comprehensive statistics about course activities.
 It helps educators and administrators track course performance and student engagement through detailed reports and metrics.

## Features

- Displays course completion rates and participation metrics
- Generates detailed reports on course activities
- Provides statistics on forums, quizzes, and general course usage
- Offers export options for statistics (CSV, Excel)
- Customizable filters and date ranges

## Requirements

- Moodle 3.x or higher
- PHP 7.2 or higher
- MySQL 5.7 or higher, or equivalent database

## Installation

1. Download the plugin.
2. Navigate to `Site Administration` > `Install Plugin`.
3. Choose the zip file and upload it.
4. Navigate to `Site Administration` > `Notifications` to complete the installation.

## Locating the Plugin

1. Navigate to `Site Administration` > `Manage Courses and Categories`.
2. The plugin will be displayed in the right panel as a block.

## Visibility and Access

- The block is accessible to users with roles such as administrator, course administrator, trainer, and editing trainer.
- The block displays statistics for courses that have statistics calculation enabled in the block configuration settings.
- After installation, administrators and teachers can view statistics for each course.

## Configuration

1. Navigate to `Site Administration` > `Plugins` > `Blocks` > `Course Statistics`.
2. Adjust the settings as needed for your site.
3. Enable courses for statistics calculation.

## Functionality Overview

- The block uses a scheduled task called 'Pre-calculate Statistics from Logstore Table' to extract information from Moodle logs and store it in its own tables.
- The task runs once a day to collect data from the logs.

## Structure

The plugin is organized into three main sections:
1. **Main Menu**: Contains navigation options.
2. **Period Filters**: Allows filtering data based on selected periods.
3. **Data Table**: Displays statistics based on the selected filters.

### General Measures

- Displays overall statistics of user activity in the course.
- Provides metrics like total time spent, number of sessions, and number of actions.

### Measures by Tool

- Displays statistics for each activity module or tool within the course.
- Provides detailed metrics for activities, such as total time dedicated and number of sessions.

### Forum Measures

- Provides detailed statistics for forum activities within the course.
- Includes metrics like number of posts, post answers, and topics initialized.

### Quiz Measures

- Displays detailed statistics for quizzes within the course.
- Provides metrics like total time spent on quizzes, number of attempts, and average scores.
