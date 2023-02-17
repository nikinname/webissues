<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2020 WebIssues Team
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

require_once( '../system/bootstrap.inc.php' );

class Client_OAuth extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_Window' );
        $this->view->setSlot( 'page_title', $this->t( 'title.Authentication' ) );
        $this->view->setSlot( 'window_size', 'small' );

        $this->isSuccessful = $this->authenticate();
    }

    private function authenticate()
    {
        $request = System_Core_Application::getInstance()->getRequest();
        $code = $request->getQueryString( 'code' );
        $state = $request->getQueryString( 'state' );

        try {
            $oauthManager = new System_Api_OAuthManager();
            $oauthManager->updateAccessToken( $code, $state );
            return true;
        } catch ( Exception $ex ) {
            $eventLog = new System_Api_EventLog();
            $eventLog->addErrorEvent( $ex );
            return false;
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_OAuth' );
