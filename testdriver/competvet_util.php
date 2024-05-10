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
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/testing/classes/util.php');
require_once($CFG->libdir . '/testing/lib.php');

// No NAMESPACE here because it confuses get_framework() in util.php.
use Behat\Gherkin\Keywords\ArrayKeywords;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Parser;
use tool_generator\local\testscenario\parsedfeature;
use tool_generator\local\testscenario\steprunner;

class competvet_util extends testing_util {
    public static $datarootskiponreset = ['.', '..', 'filedir', 'lang', 'muc', 'session'];
    /** @var array An array of original globals, restored after each test */
    protected static $globals = [];
    /** @var array of valid steps indexed by given expression tag. */
    private array $validsteps;
    private behat_data_generators $behatgenerator;

    public function __construct() {
        global $CFG, $SITE, $DB, $FULLME;
        self::$globals['_SERVER'] = $_SERVER;
        self::$globals['CFG'] = clone($CFG);
        self::$globals['SITE'] = clone($SITE);
        self::$globals['DB'] = $DB;
        self::$globals['FULLME'] = $FULLME;
    }

    public function init_test() {
        global $CFG;
        $framework = self::get_framework();
        @mkdir($CFG->dataroot . '/' . $framework, 0777, true);
        set_config('cron_enabled', 0);
        // Run all adhoc task.
        $now = time();
        while (($task = \core\task\manager::get_next_adhoc_task($now)) !== null) {
            try {
                $task->execute();
                \core\task\manager::adhoc_task_complete($task);
            } catch (Exception $e) {
                \core\task\manager::adhoc_task_failed($task);
            }
        }
        self::reset_test();
        set_config('cron_enabled', 1);
    }

    /**
     * Execute a parsed feature.
     *
     * @param parsedfeature $parsedfeature the parsed feature to execute.
     * @return bool true if all steps were executed successfully.
     */
    public function execute(parsedfeature $parsedfeature): bool {
        if (!$parsedfeature->is_valid()) {
            return false;
        }
        $result = true;
        $steps = $parsedfeature->get_all_steps();
        foreach ($steps as $step) {
            $result = $step->execute() && $result;
            if ($step->get_error()) {
                $parsedfeature->add_error($step->get_error());
            }
        }
        return $result;
    }

    /**
     * Go back to previous version
     *
     * @return void
     */
    public function reset_test() {
        global $DB, $CFG, $SITE, $COURSE, $PAGE, $OUTPUT, $SESSION, $FULLME, $FILTERLIB_PRIVATE;
        // Reset global $DB in case somebody mocked it.
        $DB = self::get_global_backup('DB');

        if ($DB->is_transaction_started()) {
            // We can not reset inside transaction.
            $DB->force_transaction_rollback();
        }

        self::reset_database();
        $localename = self::get_locale_name();
        // Restore original globals.
        $_SERVER = self::get_global_backup('_SERVER');
        $CFG = self::get_global_backup('CFG');
        $SITE = self::get_global_backup('SITE');
        $FULLME = self::get_global_backup('FULLME');
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_REQUEST = [];
        $COURSE = $SITE;

        // Reinitialise following globals.
        $OUTPUT = new bootstrap_renderer();
        $PAGE = new moodle_page();
        $FULLME = null;
        $FILTERLIB_PRIVATE = null;
        if (!empty($SESSION->notifications)) {
            $SESSION->notifications = [];
        }

        // Empty sessison and set fresh new not-logged-in user.
        \core\session\manager::init_empty_session();

        // Reset all static caches.
        accesslib_clear_all_caches(true);
        accesslib_reset_role_cache();
        get_string_manager()->reset_caches(true);
        reset_text_filters_cache(true);
        get_message_processors(false, true, true);
        filter_manager::reset_caches();
        core_filetypes::reset_caches();
        \core_search\manager::clear_static();
        core_user::reset_caches();
        \core\output\icon_system::reset_caches();
        core_courseformat\base::reset_course_cache(0);
        get_fast_modinfo(0, 0, true);

        // Restore original config once more in case resetting of caches changed CFG.
        $CFG = self::get_global_backup('CFG');

        // Inform data generator.
        self::get_data_generator()->reset();

        // Fix PHP settings.
        error_reporting($CFG->debug);

        // Reset the date/time class.
        core_date::store_default_php_timezone();
        date_default_timezone_set($CFG->timezone);

        // Make sure the time locale is consistent - that is Australian English.
        setlocale(LC_TIME, $localename);

        // Reset the log manager cache.
        get_log_manager(true);

        // Reset user agent.
        core_useragent::instance(true, null);

        self::store_versions_hash();
        self::store_database_state();
    }

    /**
     * Returns original state of global variable.
     *
     * @static
     * @param string $name
     * @return mixed
     */
    public static function get_global_backup($name) {
        if ($name === 'DB') {
            // no cloning of database object,
            // we just need the original reference, not original state
            return self::$globals['DB'];
        }
        if (isset(self::$globals[$name])) {
            if (is_object(self::$globals[$name])) {
                $return = clone(self::$globals[$name]);
                return $return;
            } else {
                return self::$globals[$name];
            }
        }
        return null;
    }

    /**
     * Gets the name of the locale for testing environment (Australian English)
     * depending on platform environment.
     *
     * @return string the locale name.
     */
    protected static function get_locale_name() {
        global $CFG;
        if ($CFG->ostype === 'WINDOWS') {
            return 'English_Australia.1252';
        } else {
            return 'en_AU.UTF-8';
        }
    }

    /**
     * Reset the database for good.
     */
    public function deinit() {
        self::reset_database();
        $framework = self::get_framework();
        $path = self::get_dataroot() . '/' . $framework . '/';
        if (!empty(trim($path, '/')) && file_exists($path)) {
            $handle = opendir($path);
            while (false !== ($item = readdir($handle))) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                if (is_dir("$path/$item")) {
                    remove_dir("$path/$item", false);
                } else {
                    unlink("$path/$item");
                }
            }
        }
        // Here we don't call reset data root as it might be on a dev site.
        cache_helper::purge_all();
        // Reset the cache API so that it recreates it's required directories as well.
        cache_factory::reset();
        // Re-enable cron.
        set_config('cron_enabled', 1);
    }

    /**
     * Initi all composer, behat libraries and load the valid steps.
     */
    public function init() {
        $this->include_composer_libraries();
        $this->include_behat_libraries();
        $this->load_generator();
    }

    /**
     * Include composer autload.
     */
    public function include_composer_libraries() {
        global $CFG;
        if (!file_exists($CFG->dirroot . '/vendor/autoload.php')) {
            throw new \moodle_exception('Missing composer.');
        }
        require_once($CFG->dirroot . '/vendor/autoload.php');
        return true;
    }

    /**
     * Include all necessary behat libraries.
     */
    public function include_behat_libraries() {
        global $CFG;
        if (!class_exists('Behat\Gherkin\Lexer')) {
            throw new \moodle_exception('Missing behat classes.');
        }
        // Behat utilities.
        require_once($CFG->libdir . '/behat/classes/util.php');
        require_once($CFG->libdir . '/behat/classes/behat_command.php');
        require_once($CFG->libdir . '/behat/behat_base.php');
        require_once("{$CFG->libdir}/tests/behat/behat_data_generators.php");
        return true;
    }

    /**
     * Load all generators.
     */
    private function load_generator() {
        $this->behatgenerator = new behat_data_generators();
        $this->validsteps = $this->scan_generator($this->behatgenerator);
    }

    /**
     * Scan a generator to get all valid steps.
     *
     * @param behat_data_generators $generator the generator to scan.
     * @return array the valid steps.
     */
    private function scan_generator(behat_data_generators $generator): array {
        $result = [];
        $class = new ReflectionClass($generator);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $given = $this->get_method_given($method);
            if ($given) {
                $result[$given] = $method->getName();
            }
        }
        return $result;
    }

    /**
     * Get the given expression tag of a method.
     *
     * @param ReflectionMethod $method the method to get the given expression tag.
     * @return string|null the given expression tag or null if not found.
     */
    private function get_method_given(ReflectionMethod $method): ?string {
        $doccomment = $method->getDocComment();
        $doccomment = str_replace("\r\n", "\n", $doccomment);
        $doccomment = str_replace("\r", "\n", $doccomment);
        $doccomment = explode("\n", $doccomment);
        foreach ($doccomment as $line) {
            $matches = [];
            if (preg_match('/.*\@(given|when|then)\s+(.+)$/i', $line, $matches)) {
                return $matches[2];
            }
        }
        return null;
    }

    /**
     * Parse a feature file.
     *
     * @param string $content the feature file content.
     * @return parsedfeature
     */
    public function parse_feature(string $content): parsedfeature {
        $result = new parsedfeature();

        $parser = $this->get_parser();
        $feature = $parser->parse($content);

        // No need for background in testing scenarios because scenarios can only contain generators.
        // In the future the background can be used to define clean up steps (when clean up methods
        // are implemented).
        if ($feature->hasScenarios()) {
            $scenarios = $feature->getScenarios();
            foreach ($scenarios as $scenario) {
                if ($scenario->getNodeType() == 'Outline') {
                    $result->add_scenario($scenario->getNodeType(), $scenario->getTitle());
                    $result->add_error(get_string('testscenario_outline', 'tool_generator'));
                    continue;
                }
                $result->add_scenario($scenario->getNodeType(), $scenario->getTitle());
                $steps = $scenario->getSteps();
                foreach ($steps as $step) {
                    $result->add_step(new steprunner($this->behatgenerator, $this->validsteps, $step));
                }
            }
        }
        return $result;
    }

    /**
     * Get the parser.
     *
     * @return Parser
     */
    private function get_parser(): Parser {
        $keywords = new ArrayKeywords([
            'en' => [
                'feature' => 'Feature',
                // If in the future we have clean up steps, background will be renamed to "Clean up".
                'background' => 'Background',
                'scenario' => 'Scenario',
                'scenario_outline' => 'Scenario Outline|Scenario Template',
                'examples' => 'Examples|Scenarios',
                'given' => 'Given',
                'when' => 'When',
                'then' => 'Then',
                'and' => 'And',
                'but' => 'But',
            ],
        ]);
        $lexer = new Lexer($keywords);
        $parser = new Parser($lexer);
        return $parser;
    }
}
