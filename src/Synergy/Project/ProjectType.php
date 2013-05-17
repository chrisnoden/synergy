<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 * 
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy\Project;

use Synergy\Singleton;

class ProjectType extends Singleton
{

    const WEB = 'web';
    const CLI = 'cli';
    const DAEMON = 'daemon';

}