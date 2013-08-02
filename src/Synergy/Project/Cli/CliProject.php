<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  File
 * @package   Synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2009-2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */
declare(ticks = 1);

namespace Synergy\Project\Cli;

use Synergy\Controller\ControllerEntity;
use Synergy\Exception\SynergyException;
use Synergy\Logger\Logger;
use Synergy\Project\ProjectAbstract;

/**
 * Class CliProject
 *
 * @category Synergy\Project\Cli
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class CliProject extends ProjectAbstract
{

    /**
     * @var string
     */
    protected $request;
    /**
     * @var array
     */
    protected $parameters = array();
    /**
     * @var ArgumentParser
     */
    protected $args;
    /**
     * @var array
     */
    protected $signals = array(
        'UNKNOWN' => 0,
        'SIGHUP' => SIGHUP,
        'SIGINT' => SIGINT,
        'SIGQUIT' => SIGQUIT,
        'SIGILL' => SIGILL,
        'SIGTRAP' => SIGTRAP,
        'SIGABRT' => SIGABRT,
        'SIGBUS' => SIGBUS,
        'SIGFPE' => SIGFPE,
        'SIGUSR1' => SIGUSR1,
        'SIGSEGV' => SIGSEGV,
        'SIGUSR2' => SIGUSR2,
        'SIGPIPE' => SIGPIPE,
        'SIGALRM' => SIGALRM,
        'SIGTERM' => SIGTERM,
        'SIGCHLD' => SIGCHLD,
        'SIGCONT' => SIGCONT,
        'SIGTSTP' => SIGTSTP,
        'SIGTTIN' => SIGTTIN,
        'SIGTTOU' => SIGTTOU,
        'SIGURG' => SIGURG,
        'SIGXCPU' => SIGXCPU,
        'SIGXFSZ' => SIGXFSZ,
        'SIGVTALRM' => SIGVTALRM,
        'SIGPROF' => SIGPROF,
        'SIGWINCH' => SIGWINCH,
        'SIGIO' => SIGIO,
        'SIGSYS' => SIGSYS,
    );


    /**
     * Instantiate a new CliProject object
     *
     * @param null  $request    the action request notation
     * @param array $parameters parameters to pass to the action
     *
     * @throws SynergyException
     */
    public function __construct($request = null, array $parameters = array())
    {
        register_tick_function(array(&$this, "checkExit"));

        // Check this is coming from the CLI
        if (PHP_SAPI !== 'cli') {
            throw new SynergyException(
                sprintf('%s must be run from command line project', __CLASS__)
            );
        }

        // Store or build the request
        $this->parameters = $parameters;
        if (!is_null($request)) {
            $this->request = $request;
        } else {
            $this->args = ArgumentParser::parseArguments();
            $this->request = $this->args->getRequest();
        }

        Logger::debug('CliProject started (pid='.getmypid().')');

        if (is_null($this->args->getRequest())) {
            Logger::emergency(
                'No controller request provided'
            );
            exit(1);
        }

        $this->registerSignalHandler();

        if ($this->args->arg('app')) {
            $this->setAppDir($this->args->arg('app'));
        }

        if ($this->args->arg('conf')) {
            $this->setConfigFilename($this->args->arg('conf'));
        }

        parent::__construct();
    }


    /**
     * Registers a signal handler method to respond
     * to any kill signals
     */
    protected  function registerSignalHandler() {
        // ignore these signals
        pcntl_signal(SIGTSTP, SIG_IGN);
        pcntl_signal(SIGTTOU, SIG_IGN);
        pcntl_signal(SIGTTIN, SIG_IGN);
        // trap these signals
        pcntl_signal(SIGHUP, array(&$this,"handleHupSignal"));
        pcntl_signal(SIGTERM, array(&$this,"handleSignals"));
        pcntl_signal(SIGINT, array(&$this,"handleSignals"));
        pcntl_signal(SIGABRT, array(&$this,"handleSignals"));
    }


    /**
     * Respond to a signal
     *
     * @param $signal
     */
    public function handleSignals($signal)
    {
        $signame = array_search($signal, $this->signals);
        if (!$signame) {
            $signame = 'UNKNOWN';
        }
        if (!SignalHandler::$blockExit) {
            Logger::critical(
                sprintf('Exiting : %s signal received', $signame)
            );
            exit;
        } else {
            Logger::warning(
                sprintf('%s signal received : Exit queued', $signame)
            );
            SignalHandler::$forceExit = true;
        }
    }


    /**
     * a SIGHUP results in the CliProject announcing itself
     *
     * @param $signal
     */
    public function handleHupSignal($signal)
    {
        if ($this->controller instanceof ControllerEntity) {
            Logger::notice(sprintf(
                '%s running since %s',
                $this->controller->getClassName().'::'.$this->controller->getMethodName().'()',
                $this->projectLaunchTime->format('r')
            ));
        }
    }


    /**
     * If an exit has been requested and we have removed the block
     * then we can now exit
     */
    public function checkExit()
    {
        if (!SignalHandler::$blockExit && SignalHandler::$forceExit) {
            exit;
        }
    }


    /**
     * Run our CLI Project
     *
     * @return void
     */
    protected function launch()
    {
        $router = new CliRouter($this->request);
        $router->match();

        /**
         * Get the ControllerEntity
         */
        $this->controller = $router->getController();
        Logger::notice(
            'Calling: '.$this->controller->getClassName().'::'.$this->controller->getMethodName()
        );
        // pass the parameters
        $this->controller->setParameters($this->parameters);
        // Call the action
        $response = $this->controller->callControllerAction();
    }


}