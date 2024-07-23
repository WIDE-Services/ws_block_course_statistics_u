<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_course_statistics\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem for block_course_statistics
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider ,
        \core_privacy\local\request\core_userlist_provider ,
        \core_privacy\local\request\plugin\provider {

    /**
     *  Returns meta data about this system.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {

        // Here you will add more items into the collection.
        $collection->add_database_table(
                'cs_user_course_sessions',
                [
                        'courseid' => 'privacy:metadata:cs_user_course_sessions:courseid',
                        'userid' => 'privacy:metadata:cs_user_course_sessions:userid',
                        'startsession' => 'privacy:metadata:cs_user_course_sessions:startsession',
                        'endsession' => 'privacy:metadata:cs_user_course_sessions:endsession',
                        'sessiontime' => 'privacy:metadata:cs_user_course_sessions:sessiontime',
                        'actions' => 'privacy:metadata:cs_user_course_sessions:actions',
                ],
                'privacy:metadata:cs_user_course_sessions'
        );

        $collection->add_database_table(
                'cs_user_activity_sessions',
                [
                        'courseid' => 'privacy:metadata:cs_user_activity_sessions:courseid',
                        'userid' => 'privacy:metadata:cs_user_activity_sessions:userid',
                        'activity' => 'privacy:metadata:cs_user_activity_sessions:activity',
                        'cminstance' => 'privacy:metadata:cs_user_activity_sessions:cminstance',
                        'activitytitle' => 'privacy:metadata:cs_user_activity_sessions:activitytitle',
                        'activitytime' => 'privacy:metadata:cs_user_activity_sessions:activitytime',
                        'activitysessions' => 'privacy:metadata:cs_user_activity_sessions:activitysessions',
                ],
                'privacy:metadata:cs_user_activity_sessions'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {

        $sql = "SELECT c.id FROM {context} c WHERE c.id= :contextid";
        $params = [
                'contextid' => 2,
        ];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof \context_system) {
            return;
        }
        $sql = "SELECT userid FROM {cs_user_course_sessions} ";
        $userlist->add_from_sql('userid', $sql);

        $sql = "SELECT userid FROM {cs_user_activity_sessions} ";
        $userlist->add_from_sql('userid', $sql);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        $context = \context_system::instance();

        $params['userid'] = $user->id;
        $params['userid2'] = $user->id;

        $participantsql = "SELECT * FROM {cs_user_course_sessions} WHERE userid= :userid";
        $recordset = $DB->get_recordset_sql($participantsql, $params);
        $recorddata = [];
        foreach ($recordset as $record) {
            $record->timecreated = date('d-m-Y H:i', $record->timecreated);
            $record->timemodified = date('d-m-Y H:i', $record->timemodified);
            $record->timeapproved = date('d-m-Y H:i', $record->timeapproved);
            $recorddata[] = (object) $record;
            writer::with_context($context)->export_data(['cs_user_course_sessions'], $record);
        }
        $recordset->close();

        $participantsql = "SELECT * FROM {cs_user_activity_sessions} WHERE userid= :userid";
        $recordset = $DB->get_recordset_sql($participantsql, $params);
        $recorddata = [];
        foreach ($recordset as $record) {
            $recorddata[] = (object) $record;
            writer::with_context($context)->export_data(['cs_user_activity_sessions'], $record);
        }
        $recordset->close();
        writer::with_context($context)->export_data([get_string('pluginname', 'block_course_statistics')],
                (object) $recorddata);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @throws dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        $userid = $user->id;

        $DB->delete_records('cs_user_course_sessions', ['userid' => $userid]);
        $DB->delete_records('cs_user_activity_sessions', ['userid' => $userid]);
        $allfiles = $DB->get_records_sql('files', ['component' => 'block_course_statistics', 'userid' => $userid]);
        foreach ($allfiles as $file) {
            $newrecord = new stdClass();
            $newrecord->id = $file->id;
            $newrecord->context = 0;
            $DB->update_record('files', $newrecord);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     * @throws dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $DB->delete_records('cs_user_course_sessions');
        $DB->delete_records('cs_user_activity_sessions');
        $allfiles = $DB->get_records_sql('files', ['component' => 'block_course_statistics']);
        foreach ($allfiles as $file) {
            $newrecord = new stdClass();
            $newrecord->id = $file->id;
            $newrecord->context = 0;
            $DB->update_record('files', $newrecord);
        }

    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     * @throws dml_exception
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $user = $userlist->get_user();
        $userid = $user->id;

        $DB->delete_records('cs_user_course_sessions', ['userid' => $userid]);
        $DB->delete_records('cs_user_activity_sessions', ['userid' => $userid]);
        $allfiles = $DB->get_records_sql('files', ['component' => 'block_course_statistics', 'userid' => $userid]);
        foreach ($allfiles as $file) {
            $newrecord = new stdClass();
            $newrecord->id = $file->id;
            $newrecord->context = 0;
            $DB->update_record('files', $newrecord);
        }

    }
}
