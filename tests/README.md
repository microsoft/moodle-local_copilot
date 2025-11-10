# Local Copilot Plugin Tests

This directory contains comprehensive PHPUnit tests for the `local_copilot` plugin, which provides Microsoft 365 Copilot integration for Moodle.

## Test Structure

The tests are organized to mirror the `classes/` folder structure for better maintainability:

```
tests/
├── external/                          # Tests for classes/external/
│   ├── get_courses_test.php
│   ├── get_course_content_test.php
│   ├── get_course_students_for_teacher_test.php
│   ├── get_activities_by_type_test.php
│   ├── get_assignment_details_test.php
│   ├── create_assignment_for_teacher_test.php
│   ├── create_content_for_teacher_test.php
│   └── get_self_enrolment_instances_for_student_test.php
├── form/                              # Tests for classes/form/
│   └── agent_configuration_form_test.php
├── local/                             # Tests for classes/local/
│   └── resource/
│       └── base_course_test.php
├── privacy/                           # Tests for classes/privacy/
│   └── provider_test.php
├── fixtures/                          # Test data and helpers
│   └── test_courses.php
├── base_testcase.php                  # Base test class
├── utils_test.php                     # Tests for utils.php
├── manifest_generator_test.php        # Tests for manifest_generator.php
├── observers_test.php                 # Tests for observers.php
└── README.md                          # This file
```

### External API Tests (`external/`)
Tests for all web service functions in `classes/external/`:
- Course retrieval and content access
- Student and activity management
- Assignment details and creation
- Content creation (announcements, forums)
- Self-enrolment discovery
- Course image management

### Form Tests (`form/`)
Tests for form classes in `classes/form/`:
- Agent configuration forms with validation
- Role-specific form behavior
- Default value population
- Error handling

### Resource Tests (`local/resource/`)
Tests for resource classes in `classes/local/resource/`:
- Course data extraction and structure
- Return structure validation
- Data formatting and transformation

### Privacy Tests (`privacy/`)
Tests for privacy compliance in `classes/privacy/`:
- GDPR metadata declarations
- User data handling (plugin stores no data)
- Privacy compliance verification

### Core Component Tests
- `utils_test.php` - Utility functions (GUID validation, URL detection, configuration)
- `manifest_generator_test.php` - Microsoft Teams manifest generation
- `observers_test.php` - Event observers and system integration

## Running the Tests

### Prerequisites
1. Moodle test environment must be set up with a test database
2. The `local_copilot` plugin must be installed
3. Required dependencies (`local_oauth2`, `webservice_restful`) must be available

### Running Individual Test Classes

```bash
# Run all Copilot tests
vendor/bin/phpunit local/copilot/tests/

# Run all external API tests
vendor/bin/phpunit local/copilot/tests/external/

# Run specific test class
vendor/bin/phpunit local/copilot/tests/utils_test.php
vendor/bin/phpunit local/copilot/tests/external/get_courses_test.php

# Run tests by category
vendor/bin/phpunit local/copilot/tests/form/           # All form tests
vendor/bin/phpunit local/copilot/tests/privacy/       # All privacy tests
vendor/bin/phpunit local/copilot/tests/local/         # All local component tests

# Run with coverage (if configured)
vendor/bin/phpunit --coverage-html coverage/ local/copilot/tests/
```

### Using Moodle's PHPUnit Runner

```bash
# From Moodle root directory
php admin/tool/phpunit/cli/run.php --testsuite local_copilot_testsuite

# Run specific test
php admin/tool/phpunit/cli/run.php local_copilot_utils_testcase
```

## Test Coverage

The test suite covers:

### Web Services (External API)
- ✅ Course retrieval with pagination
- ✅ Course content retrieval (teacher and student views)
- ✅ Course student list retrieval for teachers
- ✅ Activity retrieval by type (assignments, forums, quizzes, etc.)
- ✅ Assignment details with submissions and grades
- ✅ Assignment creation for teachers
- ✅ Content creation (announcements, forums)
- ✅ Course image setting
- ✅ Self-enrolment instance discovery
- ✅ Parameter validation for all endpoints
- ✅ Capability checking and security
- ✅ Error handling for invalid inputs
- ✅ Return structure validation
- ✅ Hidden content handling (teacher vs student views)
- ✅ Enrollment status validation

### Utility Functions
- ✅ GUID validation (with and without hyphens)
- ✅ SharePoint/OneDrive URL detection
- ✅ OAuth client management
- ✅ Configuration completeness checking
- ✅ Agent configuration data handling

### Form Handling
- ✅ Agent configuration form creation
- ✅ Form validation with valid/invalid data
- ✅ Default value population
- ✅ Role-specific form elements

### Manifest Generation
- ✅ Teams app manifest creation for teacher/student roles
- ✅ Icon handling in manifests
- ✅ Capability configuration
- ✅ Manifest validation

### Privacy Compliance
- ✅ GDPR metadata declaration
- ✅ User data export (plugin stores no user data)
- ✅ Data deletion compliance

## Test Data and Fixtures

The `fixtures/test_courses.php` file provides helper methods for:
- Creating test courses with standard settings
- Creating teacher and student users with proper enrollments
- Setting up OAuth2 clients for testing
- Configuring Copilot plugin settings
- Creating test assignments and forums
- Cleaning up test data

## Configuration Requirements

Some tests require specific Moodle configuration:

```php
// Web services must be enabled
$CFG->enablewebservices = 1;
$CFG->webserviceprotocols = 'restful';

// Authenticated users need web service capabilities
// (handled automatically by test setup)
```

## Common Test Patterns

### Testing External Functions
```php
public function test_external_function() {
    $this->resetAfterTest();
    $this->setAdminUser();
    
    // Test setup
    $course = $this->createTestCourse();
    $user = $this->createTestUser();
    
    // Execute function
    $result = external_function::execute($params);
    
    // Assertions
    $this->assertIsArray($result);
    $this->assertArrayHasKey('expected_key', $result);
}
```

### Testing Forms
```php
public function test_form_validation() {
    $this->resetAfterTest();
    
    $form = new test_form();
    $data = ['field' => 'valid_value'];
    
    $errors = $form->validation($data, []);
    $this->assertEmpty($errors);
}
```

### Testing Utilities
```php
public function test_utility_function() {
    $result = utility_class::function_name($input);
    $this->assertEquals($expected, $result);
}
```

## Troubleshooting

### Common Issues

1. **Database errors**: Ensure test database is properly configured
2. **Missing dependencies**: Check that `local_oauth2` and `webservice_restful` plugins are available
3. **Permission errors**: Test setup should handle capability assignments automatically
4. **Configuration issues**: The `base_testcase.php` class sets up required configurations

### Debugging Tips

1. Use `$this->resetAfterTest()` at the beginning of each test
2. Check that required plugins are installed and enabled
3. Verify web service configuration in test setup
4. Use `var_dump()` or `error_log()` for debugging (remove before committing)

## Contributing

When adding new tests:

1. Extend the appropriate base class (`base_test` from `base_testcase.php` for most cases)
2. Follow existing naming conventions
3. Include both positive and negative test cases
4. Test edge cases and error conditions
5. Use descriptive test method names
6. Add appropriate `@covers` annotations
7. Clean up test data in `tearDown()` or use `$this->resetAfterTest()`

## Test Database Setup

For local development, ensure your `config.php` includes test database settings:

```php
// Test database configuration
$CFG->phpunit_prefix = 'phpu_';
$CFG->phpunit_dataroot = '/path/to/moodledata_phpu';
$CFG->phpunit_dbtype = 'pgsql'; // or 'mysqli'
$CFG->phpunit_dbhost = 'localhost';
$CFG->phpunit_dbname = 'moodle_test';
$CFG->phpunit_dbuser = 'moodle';
$CFG->phpunit_dbpass = 'password';
```

Then initialize the test environment:
```bash
php admin/tool/phpunit/cli/init.php
```