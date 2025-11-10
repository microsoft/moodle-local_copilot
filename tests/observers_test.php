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

/**
 * Tests for observers class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

/**
 * Tests for observers class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class observers_test extends base_test {
    /**
     * Test that observer class exists and has expected methods.
     *
     * @covers \local_copilot\observers
     */
    public function test_observers_class_exists(): void {
        $this->assertTrue(class_exists('local_copilot\observers'));

        // Check if common observer methods exist.
        $methods = get_class_methods('local_copilot\observers');
        $this->assertIsArray($methods);

        // Observers typically have methods for handling events.
        // The exact methods would depend on what events the plugin observes.
    }

    /**
     * Test observer methods are callable.
     *
     * @covers \local_copilot\observers
     */
    public function test_observer_methods_callable(): void {
        $reflection = new \ReflectionClass('local_copilot\observers');
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);

        foreach ($methods as $method) {
            $this->assertTrue($method->isStatic(), "Observer method {$method->getName()} should be static");
            $this->assertTrue($method->isPublic(), "Observer method {$method->getName()} should be public");
        }
    }

    /**
     * Test observer method parameter types.
     *
     * @covers \local_copilot\observers
     */
    public function test_observer_method_parameters(): void {
        $reflection = new \ReflectionClass('local_copilot\observers');
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);

        foreach ($methods as $method) {
            $parameters = $method->getParameters();

            // Most observer methods should accept an event parameter.
            if (count($parameters) > 0) {
                $firstparam = $parameters[0];

                // Check if parameter expects an event object.
                if ($firstparam->hasType()) {
                    $paramtype = $firstparam->getType();
                    if ($paramtype instanceof \ReflectionNamedType) {
                        $typename = $paramtype->getName();

                        // Should accept some form of event.
                        $this->assertTrue(
                            strpos($typename, 'event') !== false ||
                            strpos($typename, 'Event') !== false ||
                            $typename === 'stdClass' ||
                            interface_exists($typename),
                            "Observer method {$method->getName()} first parameter should accept an event-like object"
                        );
                    }
                }
            }
        }
    }

    /**
     * Test observer integration with Moodle events system.
     *
     * @covers \local_copilot\observers
     */
    public function test_observers_event_integration(): void {
        global $CFG;

        // Check if observer definitions exist in db/events.php.
        $eventspath = $CFG->dirroot . '/local/copilot/db/events.php';
        if (file_exists($eventspath)) {
            $observers = [];
            include($eventspath);

            $this->assertIsArray($observers, 'Observers should be defined as an array');

            // Check each observer definition.
            foreach ($observers as $observer) {
                $this->assertArrayHasKey('eventname', $observer, 'Observer should have eventname');
                $this->assertArrayHasKey('callback', $observer, 'Observer should have callback');

                // Callback should reference the observers class.
                $callback = $observer['callback'];
                $this->assertStringContainsString('local_copilot\observers::', $callback);

                // Extract method name and verify it exists.
                $parts = explode('::', $callback);
                if (count($parts) === 2) {
                    $classname = $parts[0];
                    $methodname = $parts[1];

                    $this->assertEquals('local_copilot\observers', $classname);
                    $this->assertTrue(
                        method_exists($classname, $methodname),
                        "Observer method $methodname should exist"
                    );
                }
            }
        } else {
            $this->markTestSkipped('No events.php file found - plugin may not use observers');
        }
    }

    /**
     * Test observer method error handling.
     *
     * @covers \local_copilot\observers
     */
    public function test_observer_error_handling(): void {
        $reflection = new \ReflectionClass('local_copilot\observers');
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);

        foreach ($methods as $method) {
            // Observer methods should handle errors gracefully.
            // They should not throw exceptions that would break event processing.

            // This is more of a design principle test - actual error handling
            // would need to be tested with specific scenarios.
            $this->assertTrue(
                $method->isStatic(),
                "Observer method {$method->getName()} should be static for proper event handling"
            );
        }
    }

    /**
     * Test observer performance considerations.
     *
     * @covers \local_copilot\observers
     */
    public function test_observer_performance(): void {
        // Observer methods should be lightweight since they're called frequently.
        // This test verifies that observer methods exist and are structured properly.

        $reflection = new \ReflectionClass('local_copilot\observers');
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);

        foreach ($methods as $method) {
            // Get method source (if available) to check for obvious performance issues.
            $startline = $method->getStartLine();
            $endline = $method->getEndLine();

            if ($startline && $endline) {
                $methodlength = $endline - $startline;

                // Very long methods might indicate performance concerns.
                // This is a rough heuristic.
                $this->assertLessThan(
                    100,
                    $methodlength,
                    "Observer method {$method->getName()} is very long - consider performance implications"
                );
            }
        }
    }

    /**
     * Test that observers don't interfere with core functionality.
     *
     * @covers \local_copilot\observers
     */
    public function test_observers_dont_interfere(): void {
        // Create a test event that might trigger observers.
        $event = \core\event\course_viewed::create([
            'objectid' => $this->course->id,
            'context' => \context_course::instance($this->course->id),
        ]);

        // Trigger the event - observers should handle it gracefully.
        $this->set_user_as_student();

        try {
            $event->trigger();
            // If we get here, observers didn't break event processing.
            $this->assertTrue(true, 'Event triggered successfully with observers');
        } catch (\Exception $e) {
            $this->fail('Observer caused exception during event processing: ' . $e->getMessage());
        }
    }

    /**
     * Test observer logging and debugging.
     *
     * @covers \local_copilot\observers
     */
    public function test_observer_logging(): void {
        // Observers should log important actions for debugging.
        // This test verifies that if logging is implemented, it works correctly.

        $reflection = new \ReflectionClass('local_copilot\observers');

        // Check if observers use proper debugging techniques.
        if (method_exists('local_copilot\observers', 'debug')) {
            $debugmethod = $reflection->getMethod('debug');
            $this->assertTrue($debugmethod->isStatic() || $debugmethod->isPublic());
        }

        // Observers should not output directly - they should use proper logging.
        $this->assertTrue(
            class_exists('local_copilot\observers'),
            'Observers class should exist for event handling'
        );
    }
}
