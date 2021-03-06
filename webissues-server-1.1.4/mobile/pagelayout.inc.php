<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2015 WebIssues Team
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

class Mobile_PageLayout extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $principal = System_Api_Principal::getCurrent();

        $this->isAuthenticated = $principal->isAuthenticated();
        $this->isAdministrator = $principal->isAdministrator();
        $this->userName = $principal->getUserName();

        $this->isAnonymous = false;
        $this->canLogIn = false;
        $this->canRegister = false;

        $application = System_Core_Application::getInstance();

        $this->siteName = $this->tr( 'WebIssues' );
        try {
            if ( $application->getConnection()->isOpened() ) {
                $serverManager = new System_Api_ServerManager();
                $server = $serverManager->getServer();
                $this->siteName = $server[ 'server_name' ];

                if ( !$principal->isAuthenticated() ) {
                    $this->isAnonymous = $serverManager->getSetting( 'anonymous_access' ) == 1;
                    if ( $this->isAnonymous ) {
                        $this->canLogIn = $this->request->isRelativePathUnder( '/mobile/client' );
                        if ( $this->canLogIn )
                            $this->loginPageUrl = $application->getLoginPageUrl();
                    }
                    if ( !$this->request->isRelativePath( '/mobile/register.php' ) )
                        $this->canRegister = $serverManager->getSetting( 'self_register' ) == 1 && $serverManager->getSetting( 'email_engine' ) != null;
                }
            }
        } catch ( Exception $ex ) {
            $application->handleException( $ex );
        }

        if ( $this->request->isRelativePath( '/mobile/client/index.php' ) )
            $this->fullVersionUrl = new System_Web_RawValue( $this->filterQueryString( '/client/index.php', array( 'project', 'folder', 'type', 'issue' ) ) );
        else if ( $this->isAuthenticated || $this->canLogIn )
            $this->fullVersionUrl = '/client/index.php';
        else
            $this->fullVersionUrl = '/index.php';

        $this->pageTitle = $this->view->getSlot( 'page_title', $this->tr( 'Untitled page' ) );

        $scriptFiles = $this->view->getSlot( 'script_files' );

        $this->scriptFiles[] = '/common/js/jquery.js';
        $this->scriptFiles[] = '/common/js/jquery.cookie.js';
        $this->scriptFiles[] = '/common/js/webissues.min.js';
        $this->scriptFiles[] = '/common/js/webissues-mobile.min.js';

        if ( !empty( $scriptFiles ) ) {
            foreach ( $scriptFiles as $file )
                $this->scriptFiles[] = $file;
        }

        $cssFiles = $this->view->getSlot( 'css_files' );

        if ( !empty( $cssFiles ) ) {
            foreach ( $cssFiles as $file )
                $this->cssFiles[] = $file;
        }

        $this->cssFiles[] = '/common/theme/mobile.css';

        $inlineCode = $this->view->getSlot( 'inline_code', array() );

        $session = System_Core_Application::getInstance()->getSession();
        $path = $session->getCookiePath();
        $secure = $session->isCookieSecure() ? 'true' : 'false';

        array_unshift( $inlineCode, "
            WebIssues.switchClient( 'full', { path: '$path', expires: 90, secure: $secure, raw: true } );" );

        $this->inlineCode = new System_Web_RawValue( "    $( function() {" . join( '', $inlineCode ) . "\n    } );\n" );

        $this->icon = '/common/images/webissues.ico';

        $this->manualUrl = $application->getManualUrl();

        if ( $application->isDebugInfoEnabled() && $application->getFatalError() == null ) {
            $this->errors = array();
            foreach ( $application->getErrors() as $exception )
                $this->errors[] = $exception->__toString();
        }
    }
}
