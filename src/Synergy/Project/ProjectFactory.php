<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 * 
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy\Project;

use Synergy\Project;

/**
 * Class ProjectFactory
 * Instantiate and return the relevant ProjectType
 *
 * @package Synergy\Project
 */
final class ProjectFactory
{

    /**
     * Create a new Synergy Project
     *
     * @param                          $projectName
     * @param                          $projectType
     * @param \Psr\Log\LoggerInterface $Logger
     * @param array                    $options
     * @return \Synergy\Project\ProjectAbstract
     * @throws \Synergy\Exception\InvalidProjectTypeException
     */
    public static function build(
            $projectName,
            $projectType,
            \Psr\Log\LoggerInterface $Logger,
            array $options = array()
        )
    {
        Project::init();
        Project::setLogger($Logger);
        Project::setName($projectName);
        Project::setType($projectType);
        Project::setOptions($options);
        return Project::getObject();
    }


}