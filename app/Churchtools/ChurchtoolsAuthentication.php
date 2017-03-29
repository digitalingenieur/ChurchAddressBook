<?php

/**
 * Contao Churchtools-Bundle
 *
 * Copyright (c) 2016 Samuel Heer
 *
 * @license LGPL-3.0+
 */

namespace Churchtools

/**
 * Provide function to authenticate against Churchtools.
 *
 * @author Samuel Heer <https://github.com/digitalingenieur>
 */
class ChurchtoolsAuthentication{


	public function checkCredentials($strUsername, $strPassword, \Contao\User $objUser)
	{
		
			try{
				$api = new ChurchtoolsApi($strUsername);

			} catch(\Exception $e){
				echo $e->getMessage();
				return false;
			}
			//return true;
	}

	public function importUser($strUsername, $strPassword, $strTable)
	{
		if ($strTable == 'tl_user')
	    {
	        // Import user from an LDAP server
	        if ($this->importUserFromChurchtools($strUsername, $strPassword))
	        {
	            return true;
	        }
	    }

	    return false;
		}
}