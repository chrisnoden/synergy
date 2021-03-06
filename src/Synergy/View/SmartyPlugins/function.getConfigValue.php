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
 * @category  Smarty Function
 * @package   Synergy MVC Library
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */


/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.getConfigValue.php
 * Type:     function
 * Name:     getConfigValue
 * Purpose:  get a value from the config file
 * -------------------------------------------------------------
 */
function smarty_function_getConfigValue($params, Smarty_Internal_Template $template)
{
    if (isset($params['keyname'])) {
        $project = \Synergy\Project::getObject();
        if ($project instanceof \Synergy\Project\ProjectAbstract)
        {
            $template->assign($params['assign'], $project->getOption($params['keyname']));
        }
    } else {
        throw new \Synergy\Exception\TemplateFunctionException(
            'getConfigValue function requires a keyname to search for'
        );
    }
}

