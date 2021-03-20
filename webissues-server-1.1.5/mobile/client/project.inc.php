<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2017 WebIssues Team
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/

if ( !defined( 'WI_VERSION' ) ) die( -1 );

class Mobile_Client_Project extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();
        $projectId = (int)$this->request->getQueryString( 'project' );
        $project = $projectManager->getProject( $projectId );

        $this->projectName = $project[ 'project_name' ];

        $this->view->setSlot( 'page_title', $project[ 'project_name' ] );

        $prettyPrint = false;

        if ( $project[ 'descr_id' ] != null ) {
            $formatter = new System_Api_Formatter();

            $this->descr = $projectManager->getProjectDescription( $project );
            $this->descr[ 'modified_date' ] = $formatter->formatDateTime( $this->descr[ 'modified_date' ], System_Api_Formatter::ToLocalTimeZone );
            if ( $this->descr[ 'descr_format' ] == System_Const::TextWithMarkup )
                $this->descr[ 'descr_text' ] = System_Web_MarkupProcessor::convertToRawHtml( $this->descr[ 'descr_text' ], $prettyPrint );
            else
                $this->descr[ 'descr_text' ] = System_Web_LinkLocator::convertToRawHtml( $this->descr[ 'descr_text' ] );
        }

        if ( $prettyPrint ) {
            $script = new System_Web_JavaScript( $this->view );
            $script->registerPrettyPrint();
        }
    }
}
