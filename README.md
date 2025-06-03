# Local Copilot Plugin for Moodle

The **Local Copilot Plugin** is a Moodle plugin that supports integration with Microsoft 365 Copilot to improve teacher and student experience by providing detailed course content, sections, and activities through an API.

## Features

The plugin provides interfaces for site administrators to configure and download Microsoft Teams apps containing agents for teachers and students, and API functions to respond to requests from Microsoft 365 Copilot about courses, activities, as well as grades and progress.

### API functions
- `local_copilot_get_courses`: Returns all courses that a user is enrolled in.
- `local_copilot_get_course_students_for_teacher`: (Disabled) Return all students enrolled in a course for a teacher.
- `local_copilot_get_course_content_for_teacher`: Returns course details, all sections, and activities in each section for a teacher.
- `local_copilot_get_course_content_for_student`: Returns course details, all sections, and activities in each section for a student.
- `local_copilot_get_activities_by_type_for_teacher`: Returns all activities of the given type for a teacher.
- `local_copilot_get_activities_by_type_for_student`: Returns all activities of the given type for a student.
- `local_copilot_get_assignment_details_for_teacher`: Returns assignment activity details, list of submissions along with grade details from all students for a teacher.
- `local_copilot_get_assignment_details_for_student`: Returns assignment activity metadata, submission, and grade details of an assignment for a student.
- `local_copilot_set_course_image_for_teacher`: (Disabled) Updates course image from URL.
- `local_copilot_get_self_enrolment_instances_for_student`: Returns self enrolment instances for a student.
- `local_copilot_create_assignment_for_teacher`: Creates an assignment activity in a given course.
- `local_copilot_create_announcement_for_teacher`: Creates an announcement post in a given course.
- `local_copilot_create_forum_for_teacher`: Creates a forum activity in a given course.

## Installation

1. Clone or download this plugin into the `local/copilot` directory of your Moodle installation
2. Navigate to your Moodle site's administration area to run the upgrade process

## Configuration

1. Configure OAuth2 client registrations for teacher and student roles in [Oauth2 Server plugin](https://moodle.org/plugins/local_oauth2)
2. Complete basic configuration on the plugin settings page.
3. Configure teacher and student agent settings.
4. Download teacher and student Microsoft Teams apps containing agents.
5. Upload the apps to Microsoft Teams, and configure access.

## Usage

1. Once the plugin is installed and configured, teachers and students can access the Copilot features through Microsoft Teams.
2. When the Copilot is invoked, it will retrieve course and activity information from Moodle using the provided API.

## Requirements

- Moodle 4.5 or higher
- Microsoft 365 Copilot licence to use the Copilot features

## Dependencies

- [Oauth2 Server](https://moodle.org/plugins/local_oauth2) plugin installed and configured
- [RESTful protocol](https://moodle.org/plugins/webservice_restful) plugin installed and enabled

## License

This plugin is licensed under the MIT License. See [LICENSE](LICENSE).

## Support

For issues, questions, or feature requests, please open an issue in the plugin's repository or contact the development team.

## Brand

The word Moodle and associated Moodle logos are trademarks or registered trademarks of Moodle Pty Ltd or its related affiliates.
