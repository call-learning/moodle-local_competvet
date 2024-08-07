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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

// Special thanks to Moodle HQ & Noel De Martin for the following snippet.
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ExpectationException;

class behat_competvet_app extends behat_base {
    protected $featurepath = '';
    /**
     * @var false
     */
    private bool $apprunning = false;

    /**
     * Opens the Moodle App in the browser.
     *
     * @When I launch the app :runtime
     * @When I launch the app
     * @param string $runtime Runtime
     * @throws DriverException Issue with configuration or feature file
     * @throws dml_exception Problem with Moodle setup
     * @throws ExpectationException Problem with resizing window
     */
    public function i_launch_the_app(string $runtime = '') {
        // Go to page and prepare browser for app.
        $this->prepare_browser(['skiponboarding' => empty($runtime)]);
    }

    /**
     * Goes to the app page and then sets up some initial JavaScript so we can use it.
     *
     * @param string $url App URL
     * @throws DriverException If the app fails to load properly
     */
    protected function prepare_browser(array $options = []) {
        if ($this->evaluate_script('window.behat') && $this->runtime_js('hasInitialized()')) {
            // Already initialized.
            return;
        }

        $restart = false;

        if (!$this->apprunning) {
            $this->check_tags();

            $restart = true;

            // Reset its size.
            $this->resize_app_window();

            // Visit the Ionic URL.
            $this->getSession()->visit($this->get_app_url());
            $this->notify_load();

            $this->apprunning = true;
        }

        // Wait the application to load.
        $this->spin(function($context) {
            $title = $context->getSession()->getPage()->find('xpath', '//title');

            if ($title) {
                $text = $title->getHtml();

                if ($text === 'CompetVet') {
                    return true;
                }
            }

            throw new DriverException('CompetVet not found in browser');
        }, false, 60);

        try {
            // Init Behat JavaScript runtime.
            $initoptions = json_encode([
                'skipOnBoarding' => $options['skiponboarding'] ?? true,
                'configOverrides' => $this->appconfig,
            ]);

            $this->runtime_js("init($initoptions)");
        } catch (Exception $error) {
            throw new DriverException('CompetVet not running or not running on Automated mode: ' . $error->getMessage());
        }

        if ($restart) {
            // Assert initial page.
            $this->spin(function($context) {
                $page = $context->getSession()->getPage();
                $element = $page->find('xpath', '//page-core-login-site');

                if ($element) {
                    // Login screen found.
                    return true;
                }

                if ($page->find('xpath', '//page-core-mainmenu')) {
                    // Main menu found.
                    return true;
                }

                throw new DriverException('CompetVet not launched properly');
            }, false, 60);
        }

        // Continue only after JS finishes.
        $this->wait_for_pending_js();
    }

    /**
     * Get url of the running CompetVet application.
     *
     * @return string Ionic app url.
     */
    public function get_app_url(): string {
        global $CFG;

        if (empty($CFG->behat_competvetapp_wwwroot)) {
            throw new DriverException('$CFG->behat_competvetapp_wwwroot must be defined.');
        }

        return $CFG->behat_competvetapp_wwwroot;
    }

    /**
     * @BeforeScenario
     */
    public function before_scenario(ScenarioScope $scope) {
        $feature = $scope->getFeature();

        if (!$feature->hasTag('competvet_app')) {
            return;
        }

        $this->featurepath = dirname($feature->getFile());
    }

    /**
     * Restart the app.
     *
     * @When I restart the app
     */
    public function i_restart_the_app() {
        $this->getSession()->visit($this->get_app_url());

        $this->i_wait_the_app_to_restart();
    }

    /**
     * @Then I wait the app to restart
     */
    public function i_wait_the_app_to_restart() {
        // Prepare testing runtime again.
        $this->prepare_browser();
    }

    /**
     * @Then I log out in the app
     *
     * @param bool $force If force logout or not.
     */
    public function i_log_out_in_app($force = true) {
        $options = json_encode([
            'forceLogout' => $force,
        ]);

        $result = $this->zone_js("sites.logout($options)");

        if ($result !== 'OK') {
            throw new DriverException('Error on log out - ' . $result);
        }

        $this->i_wait_the_app_to_restart();
    }

    /**
     * Called from behat_hooks when a new scenario starts, if it has the app tag.
     *
     * This updates Moodle configuration and starts Ionic running, if it isn't already.
     */
    public function start_scenario() {
        $this->check_behat_setup();

        if ($this->apprunning) {
            $this->apprunning = false;
        }
    }

    /**
     * Checks the Behat setup - tags and configuration.
     *
     * @throws DriverException
     */
    protected function check_behat_setup() {
        global $CFG;

        // Check JavaScript is enabled.
        if (!$this->running_javascript()) {
            throw new DriverException('The app requires JavaScript.');
        }

        // Check the config settings are defined.
        if (empty($CFG->behat_competvetapp_wwwroot)) {
            throw new DriverException('$CFG->behat_competvetapp_wwwroot must be defined.');
        }
    }

    /**
     * This function will skip scenarios based on @lms_from and @lms_upto tags and also missing @app tags.
     */
    public function check_tags() {
        if (!$this->has_tag('competvet_app')) {
            throw new DriverException('Requires @competvet_app tag on scenario or feature.');
        }
    }

    /**
     * Resize window to have app dimensions.
     */
    protected function resize_app_window(int $width = 500, int $height = 720) {
        $offset = $this->evaluate_script("{
            x: window.outerWidth - document.body.offsetWidth,
            y: window.outerHeight - window.innerHeight,
        }");

        $this->getSession()->getDriver()->resizeWindow($width + $offset['x'], $height + $offset['y']);
    }

}
